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
use Sunrise\Hydrator\Annotation\Subtype;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Exception\UnsupportedPropertyTypeException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function is_array;
use function is_subclass_of;

/**
 * @since 3.1.0
 *
 * @psalm-suppress MissingConstructor
 */
final class ArrayTypeConverter implements TypeConverterInterface, AnnotationReaderAwareInterface, HydratorAwareInterface
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
    public function castValue($value, Type $type, array $path): Generator
    {
        $containerName = $type->getName();
        if ($containerName <> BuiltinType::ARRAY && !is_subclass_of($containerName, ArrayAccess::class)) {
            return;
        }

        if (!is_array($value)) {
            throw InvalidValueException::shouldBeArray($path);
        }

        $container = [];
        if ($containerName <> BuiltinType::ARRAY) {
            $containerReflection = new ReflectionClass($containerName);
            if (!$containerReflection->isInstantiable()) {
                throw UnsupportedPropertyTypeException::nonInstantiableClass($type->getHolder(), $containerName);
            }

            $container = $containerReflection->newInstanceWithoutConstructor();
        }

        $valueSubtype = $this->annotationReader->getAnnotations($type->getHolder(), Subtype::class)->current();
        if ($valueSubtype === null) {
            $elementCounter = 0;
            foreach ($value as $key => $element) {
                try {
                    $container[$key] = $element;
                    ++$elementCounter;
                } catch (OverflowException $e) {
                    throw InvalidValueException::redundantElement([...$path, $key], $elementCounter);
                }
            }

            return yield $container;
        }

        $elementCounter = 0;
        $elementType = new Type($type->getHolder(), $valueSubtype->name, false);
        $violations = [];
        foreach ($value as $key => $element) {
            if (isset($valueSubtype->limit) && $elementCounter >= $valueSubtype->limit) {
                $violations[] = InvalidValueException::redundantElement([...$path, $key], $valueSubtype->limit);
                break;
            }

            try {
                $container[$key] = $this->hydrator->castValue($element, $elementType, [...$path, $key]);
                ++$elementCounter;
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            } catch (OverflowException $e) {
                $violations[] = InvalidValueException::redundantElement([...$path, $key], $elementCounter);
                break;
            }
        }

        if (!empty($violations)) {
            throw new InvalidDataException('Invalid data.', $violations);
        }

        yield $container;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 20;
    }
}
