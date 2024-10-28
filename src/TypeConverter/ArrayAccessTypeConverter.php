<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2021, Anatoly Nekhay
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
use Sunrise\Hydrator\Annotation\Subtype;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function count;
use function end;
use function get_object_vars;
use function is_array;
use function is_subclass_of;

/**
 * @since 3.1.0
 *
 * @psalm-suppress MissingConstructor
 */
final class ArrayAccessTypeConverter implements
    TypeConverterInterface,
    AnnotationReaderAwareInterface,
    HydratorAwareInterface
{

    /**
     * @var AnnotationReaderInterface
     */
    private AnnotationReaderInterface $annotationReader;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @inheritDoc
     */
    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritDoc
     */
    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritDoc
     *
     * @psalm-suppress MixedAssignment
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        $typeName = $type->getName();
        if (!is_subclass_of($typeName, ArrayAccess::class)) {
            return;
        }

        $containerReflection = new ReflectionClass($typeName);
        if (!$containerReflection->isInstantiable()) {
            throw InvalidObjectException::unsupportedType($type);
        }

        $container = $containerReflection->newInstanceWithoutConstructor();

        // https://www.php.net/stdClass
        if ($value instanceof stdClass) {
            $value = get_object_vars($value);
        }

        if ($value === []) {
            return yield $container;
        }

        if (!is_array($value)) {
            throw InvalidValueException::mustBeArray($path);
        }

        $subtype = $this->annotationReader->getAnnotations(Subtype::class, $type->getHolder())->current()
            ?? self::getContainerSubtype($containerReflection);

        if ($subtype === null) {
            $counter = 0;
            foreach ($value as $key => $element) {
                try {
                    $container[$key] = $element;
                    ++$counter;
                } catch (OverflowException $e) {
                    throw InvalidValueException::arrayOverflow($path, $counter);
                }
            }

            return yield $container;
        }

        if ($subtype->limit !== null && count($value) > $subtype->limit) {
            throw InvalidValueException::arrayOverflow($path, $subtype->limit);
        }

        $subtype->holder ??= $type->getHolder();

        $counter = 0;
        $violations = [];
        foreach ($value as $key => $element) {
            try {
                $container[$key] = $this->hydrator->castValue(
                    $element,
                    new Type($subtype->holder, $subtype->name, $subtype->allowsNull),
                    [...$path, $key],
                    $context,
                );

                $counter++;
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            } catch (OverflowException $e) {
                $violations[] = InvalidValueException::arrayOverflow($path, $counter);
                break;
            }
        }

        if ($violations !== []) {
            throw new InvalidDataException('Invalid data', $violations);
        }

        yield $container;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 10;
    }

    /**
     * Gets a subtype from the given container's constructor
     *
     * @param ReflectionClass<ArrayAccess> $class
     *
     * @return Subtype|null
     *
     * @codeCoverageIgnore
     */
    private static function getContainerSubtype(ReflectionClass $class): ?Subtype
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return null;
        }

        $constructorParameters = $constructor->getParameters();
        if ($constructorParameters === []) {
            return null;
        }

        $lastConstructorParameter = end($constructorParameters);
        if ($lastConstructorParameter->isVariadic() === false) {
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

        $subtype = new Subtype(
            $lastConstructorParameterTypeName,
            $lastConstructorParameterType->allowsNull(),
        );

        $subtype->holder = $lastConstructorParameter;

        return $subtype;
    }
}
