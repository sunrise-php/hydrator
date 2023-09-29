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

namespace Sunrise\Hydrator;

use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Exception\UnsupportedPropertyTypeException;

/**
 * HydratorInterface
 */
interface HydratorInterface
{

    /**
     * Adds the given type converter(s) to the hydrator
     *
     * @param TypeConverterInterface ...$typeConverters
     *
     * @return void
     *
     * @since 3.1.0
     */
    public function addTypeConverter(TypeConverterInterface ...$typeConverters): void;

    /**
     * Tries to cast the given value to the given type
     *
     * @param mixed $value
     * @param Type $type
     * @param list<array-key> $path
     *
     * @return mixed
     *
     * @throws InvalidDataException If one of the value elements isn't valid.
     *
     * @throws InvalidValueException If the value isn't valid.
     *
     * @throws UnsupportedPropertyTypeException If the type isn't supported.
     *
     * @since 3.1.0
     */
    public function castValue($value, Type $type, array $path);

    /**
     * Hydrates the given object with the given data
     *
     * @param class-string<T>|T $object
     * @param array<array-key, mixed> $data
     * @param list<array-key> $path
     *
     * @return T
     *
     * @throws InvalidDataException If the given data is invalid.
     *
     * @throws InvalidObjectException If the given object is invalid.
     *
     * @template T of object
     */
    public function hydrate($object, array $data, array $path = []): object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string<T>|T $object
     * @param string $json
     * @param int<0, max> $flags
     * @param int<1, 2147483647> $depth
     * @param list<array-key> $path
     *
     * @return T
     *
     * @throws InvalidDataException If the given data is invalid.
     *
     * @throws InvalidObjectException If the given object is invalid.
     *
     * @template T of object
     */
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512, array $path = []): object;
}
