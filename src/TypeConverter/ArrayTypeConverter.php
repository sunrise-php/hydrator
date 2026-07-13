<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2021, Anatolii Nekhai
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

declare(strict_types=1);

namespace Sunrise\Hydrator\TypeConverter;

use Generator;
use phpDocumentor\Reflection as PhpDoc;
use ReflectionProperty;
use stdClass;
use Sunrise\Hydrator\Annotation\ItemType;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

/**
 * @since 3.1.0
 */
final class ArrayTypeConverter implements
    TypeConverterInterface,
    AnnotationReaderAwareInterface,
    HydratorAwareInterface
{
    private AnnotationReaderInterface $annotationReader;
    private HydratorInterface $hydrator;

    private bool $isDocBlockReaderEnabled;
    private ?PhpDoc\DocBlockFactoryInterface $docBlockFactory = null;
    private ?PhpDoc\Types\ContextFactory $docBlockContextFactory = null;
    /** @var array<string, ItemType|null> */
    private array $dockBlockReaderCache = [];

    public function __construct(bool $isDocBlockReaderEnabled = false)
    {
        $this->isDocBlockReaderEnabled = $isDocBlockReaderEnabled;
    }

    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() !== BuiltinType::ARRAY) {
            return;
        }

        // https://www.php.net/stdClass
        if ($value instanceof stdClass) {
            $value = \get_object_vars($value);
        }

        if (!\is_array($value)) {
            throw InvalidValueException::mustBeArray($path, $value);
        }

        if (empty($value)) {
            yield $value;
            return;
        }

        $typeHolder = $type->getHolder();
        if ($typeHolder === null) {
            yield $value;
            return;
        }

        /** @var ItemType|null $itemType */
        $itemType = $this->annotationReader->getAnnotations(ItemType::class, $typeHolder)->current();
        $itemType ??= $this->getItemTypeFromVarTag($typeHolder);

        if ($itemType === null) {
            yield $value;
            return;
        }

        $itemType->holder ??= $type->getHolder();

        if ($itemType->limit !== null && \count($value) > $itemType->limit) {
            throw InvalidValueException::arrayOverflow($path, $itemType->limit, $value);
        }

        $violations = [];
        foreach ($value as $key => $item) {
            try {
                $value[$key] = $this->hydrator->castValue(
                    $item,
                    new Type($itemType->holder, $itemType->name, $itemType->allowsNull),
                    [...$path, $key],
                    $context,
                );
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            }
        }

        if ($violations !== []) {
            throw new InvalidDataException('Invalid data', $violations);
        }

        yield $value;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 20;
    }

    /**
     * @param mixed $holder
     */
    private function getItemTypeFromVarTag($holder): ?ItemType
    {
        if (! $this->isDocBlockReaderEnabled) {
            return null;
        }

        $this->docBlockFactory ??= PhpDoc\DocBlockFactory::createInstance();
        $this->docBlockContextFactory ??= new PhpDoc\Types\ContextFactory();

        if (! $holder instanceof ReflectionProperty) {
            return null;
        }

        $cacheKey = \sprintf('%s::$%s', $holder->getDeclaringClass()->getName(), $holder->getName());
        if (\array_key_exists($cacheKey, $this->dockBlockReaderCache)) {
            return $this->dockBlockReaderCache[$cacheKey];
        }

        $this->dockBlockReaderCache[$cacheKey] = null;

        $docComment = $holder->getDocComment();
        if ($docComment === false) {
            return null;
        }

        $docBlock = $this->docBlockFactory->create(
            $docComment,
            $this->docBlockContextFactory->createFromReflector($holder),
        );

        $varTag = $docBlock->getTagsByName('var')[0] ?? null;
        if (! $varTag instanceof PhpDoc\DocBlock\Tags\Var_) {
            return null;
        }

        $varType = $varTag->getType();
        if ($varType === null) {
            return null;
        }

        $varType = self::unwrapNullablePhpDocType($varType);
        if (! $varType instanceof PhpDoc\Types\AbstractList) {
            return null;
        }

        $phpDocItemType = $varType->getValueType();
        if ($phpDocItemType instanceof PhpDoc\Types\Mixed_) {
            return null;
        }

        /** @var non-empty-string $itemTypeName */
        $itemTypeName = \ltrim((string) self::unwrapNullablePhpDocType($phpDocItemType), '\\');
        $isItemTypeNullable = self::isPhpDocTypeNullable($phpDocItemType);
        $this->dockBlockReaderCache[$cacheKey] = new ItemType($itemTypeName, $isItemTypeNullable);

        return $this->dockBlockReaderCache[$cacheKey];
    }

    private static function isPhpDocTypeNullable(PhpDoc\Type $phpDocType): bool
    {
        if ($phpDocType instanceof PhpDoc\Types\Nullable) {
            return true;
        }

        if ($phpDocType instanceof PhpDoc\Types\Compound) {
            foreach ($phpDocType as $type) {
                if ($type instanceof PhpDoc\Types\Null_) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function unwrapNullablePhpDocType(PhpDoc\Type $phpDocType): PhpDoc\Type
    {
        if ($phpDocType instanceof PhpDoc\Types\Nullable) {
            return $phpDocType->getActualType();
        }

        if ($phpDocType instanceof PhpDoc\Types\Compound) {
            $notNullablePhpDocTypes = [];
            foreach ($phpDocType as $compoundPhpDocTypeItem) {
                if (! $compoundPhpDocTypeItem instanceof PhpDoc\Types\Null_) {
                    $notNullablePhpDocTypes[] = $compoundPhpDocTypeItem;
                }
            }

            // The hydrator doesn't support the compound type.
            if (\count($notNullablePhpDocTypes) === 1) {
                return $notNullablePhpDocTypes[0];
            }
        }

        return $phpDocType;
    }
}
