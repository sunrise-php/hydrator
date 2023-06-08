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

/**
 * HydratorInterface
 */
interface HydratorInterface
{

    /**
     * Hydrates the given object with the given data
     *
     * @param class-string<T>|T $object
     * @param array<array-key, mixed> $data
     *
     * @return T
     *
     * @throws InvalidDataException
     *         If the given data is invalid.
     *
     * @throws InvalidObjectException
     *         If the given object is invalid.
     *
     * @template T of object
     */
    public function hydrate($object, array $data): object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string<T>|T $object
     * @param string $json
     * @param int<0, max> $flags
     * @param int<1, 2147483647> $depth
     *
     * @return T
     *
     * @throws InvalidDataException
     *         If the given data is invalid.
     *
     * @throws InvalidObjectException
     *         If the given object is invalid.
     *
     * @template T of object
     */
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512): object;
}
