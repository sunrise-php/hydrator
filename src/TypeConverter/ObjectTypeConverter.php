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
final class ObjectTypeConverter implements TypeConverterInterface, HydratorAwareInterface
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
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        $className = $type->getName();
        if (!class_exists($className)) {
            return;
        }

        $class = new ReflectionClass($className);
        if ($class->isInternal() || !$class->isInstantiable()) {
            return;
        }

        if (!is_array($value)) {
            throw InvalidValueException::mustBeArray($path);
        }

        $object = $class->newInstanceWithoutConstructor();

        yield $this->hydrator->hydrate($object, $value, $path, $context);
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return -100;
    }
}
