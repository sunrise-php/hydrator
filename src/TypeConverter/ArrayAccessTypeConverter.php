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

use ArrayAccess;
use Generator;
use OverflowException;
use ReflectionClass;
use ReflectionNamedType;
use stdClass;
use Sunrise\Hydrator\Annotation\ItemType;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

/**
 * @since 3.1.0
 */
final class ArrayAccessTypeConverter implements
    TypeConverterInterface,
    AnnotationReaderAwareInterface,
    HydratorAwareInterface
{
    private AnnotationReaderInterface $annotationReader;
    private HydratorInterface $hydrator;

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
        $className = $type->getName();
        if (!\is_subclass_of($className, ArrayAccess::class)) {
            return;
        }

        /** @var ReflectionClass<ArrayAccess<array-key, mixed>> $class */
        $class = new ReflectionClass($className);
        if (!$class->isInstantiable()) {
            throw InvalidObjectException::unsupportedType($type);
        }

        // https://www.php.net/stdClass
        if ($value instanceof stdClass) {
            $value = \get_object_vars($value);
        }

        if (!\is_array($value)) {
            throw InvalidValueException::mustBeArray($path, $value);
        }

        $collection = $class->newInstanceWithoutConstructor();

        if (empty($value)) {
            yield $collection;
            return;
        }

        /** @phpstan-var ItemType|null $itemType */
        $itemType = $this->annotationReader->getAnnotations(ItemType::class, $type->getHolder())->current()
            ?? self::getItemTypeFromCollectionConstructor($class);

        $itemType->holder ??= $type->getHolder();

        if ($itemType === null) {
            $counter = 0;
            foreach ($value as $key => $item) {
                try {
                    $collection[$key] = $item;
                    ++$counter;
                } catch (OverflowException $e) {
                    throw InvalidValueException::arrayOverflow($path, $counter, $value);
                }
            }

            yield $collection;
            return;
        }

        if ($itemType->limit !== null && \count($value) > $itemType->limit) {
            throw InvalidValueException::arrayOverflow($path, $itemType->limit, $value);
        }

        $counter = 0;
        $violations = [];
        foreach ($value as $key => $item) {
            try {
                $collection[$key] = $this->hydrator->castValue(
                    $item,
                    new Type($itemType->holder, $itemType->name, $itemType->allowsNull),
                    [...$path, $key],
                    $context,
                );
                $counter++;
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            } catch (OverflowException $e) {
                $violations[] = InvalidValueException::arrayOverflow($path, $counter, $value);
                break;
            }
        }

        if ($violations !== []) {
            throw new InvalidDataException('Invalid data', $violations);
        }

        yield $collection;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 10;
    }

    /**
     * @param ReflectionClass<ArrayAccess<array-key, mixed>> $class
     *
     * @codeCoverageIgnore
     */
    private static function getItemTypeFromCollectionConstructor(ReflectionClass $class): ?ItemType
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return null;
        }

        $constructorParameters = $constructor->getParameters();
        if ($constructorParameters === []) {
            return null;
        }

        $lastConstructorParameter = \end($constructorParameters);
        if (!$lastConstructorParameter->isVariadic()) {
            return null;
        }

        $lastConstructorParameterType = $lastConstructorParameter->getType();
        if ($lastConstructorParameterType === null) {
            return null;
        }

        /** @var non-empty-string $lastConstructorParameterTypeName */
        $lastConstructorParameterTypeName = ($lastConstructorParameterType instanceof ReflectionNamedType)
            ? $lastConstructorParameterType->getName()
            : (string) $lastConstructorParameterType;

        $itemType = new ItemType(
            $lastConstructorParameterTypeName,
            $lastConstructorParameterType->allowsNull(),
        );

        $itemType->holder = $lastConstructorParameter;

        return $itemType;
    }
}
