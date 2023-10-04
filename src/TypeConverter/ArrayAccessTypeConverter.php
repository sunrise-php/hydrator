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
        $containerName = $type->getName();
        if (!is_subclass_of($containerName, ArrayAccess::class)) {
            return;
        }

        $containerReflection = new ReflectionClass($containerName);
        if (!$containerReflection->isInstantiable()) {
            throw InvalidObjectException::unsupportedType($type);
        }

        $container = $containerReflection->newInstanceWithoutConstructor();

        if ($value === []) {
            return yield $container;
        }

        if (!is_array($value)) {
            throw InvalidValueException::mustBeArray($path);
        }

        $subtype = $this->annotationReader->getAnnotations(Subtype::class, $type->getHolder())->current();

        $subtype ??= $this->getContainerSubtype($containerReflection);

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

        if (isset($subtype->limit) && count($value) > $subtype->limit) {
            throw InvalidValueException::arrayOverflow($path, $subtype->limit);
        }

        $counter = 0;
        $violations = [];
        foreach ($value as $key => $element) {
            try {
                $container[$key] = $this->hydrator->castValue(
                    $element,
                    new Type($type->getHolder(), $subtype->name, false),
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

        if ($violations === []) {
            return yield $container;
        }

        throw new InvalidDataException('Invalid data', $violations);
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
     * @param ReflectionClass $container
     *
     * @return Subtype|null
     */
    private function getContainerSubtype(ReflectionClass $container): ?Subtype
    {
        $constructor = $container->getConstructor();
        if ($constructor === null) {
            return null;
        }

        $parameters = $constructor->getParameters();
        if (count($parameters) <> 1) {
            return null;
        }

        $parameter = $parameters[0];
        if (!$parameter->isVariadic()) {
            return null;
        }

        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType) {
            return null;
        }

        return new Subtype($type->getName());
    }
}
