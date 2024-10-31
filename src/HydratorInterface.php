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

interface HydratorInterface
{
    /**
     * Tries to cast the given value to the given type
     *
     * @param mixed $value
     * @param list<array-key> $path
     * @param array<string, mixed> $context
     *
     * @return mixed
     *
     * @throws InvalidObjectException
     * @throws InvalidValueException
     * @throws InvalidDataException
     *
     * @since 3.1.0
     */
    public function castValue($value, Type $type, array $path = [], array $context = []);

    /**
     * Hydrates the given object with the given data
     *
     * @param class-string<T>|T $object
     * @param array<array-key, mixed> $data
     * @param list<array-key> $path
     * @param array<string, mixed> $context
     *
     * @return T
     *
     * @throws InvalidObjectException
     * @throws InvalidDataException
     *
     * @template T of object
     */
    public function hydrate($object, array $data, array $path = [], array $context = []): object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string<T>|T $object
     * @param int<0, max> $flags
     * @param int<1, 2147483647> $depth
     * @param list<array-key> $path
     * @param array<string, mixed> $context
     *
     * @return T
     *
     * @throws InvalidObjectException
     * @throws InvalidDataException
     *
     * @template T of object
     */
    // phpcs:ignore Generic.Files.LineLength
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512, array $path = [], array $context = []): object;
}
