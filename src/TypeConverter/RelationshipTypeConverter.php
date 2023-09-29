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

use Generator;
use ReflectionClass;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Exception\UnsupportedPropertyTypeException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function class_exists;
use function is_array;

/**
 * @since 3.1.0
 *
 * @psalm-suppress MissingConstructor
 */
final class RelationshipTypeConverter implements TypeConverterInterface, HydratorAwareInterface
{

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @inheritDoc
     */
    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path): Generator
    {
        $className = $type->getName();
        if (!class_exists($className)) {
            return;
        }

        $classReflection = new ReflectionClass($className);
        if ($classReflection->isInternal()) {
            return;
        }

        if (!$classReflection->isInstantiable()) {
            throw UnsupportedPropertyTypeException::nonInstantiableClass($type->getHolder(), $className);
        }

        if (!is_array($value)) {
            throw InvalidValueException::shouldBeArray($path);
        }

        $classInstance = $classReflection->newInstanceWithoutConstructor();

        yield $this->hydrator->hydrate($classInstance, $value, $path);
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return -100;
    }
}
