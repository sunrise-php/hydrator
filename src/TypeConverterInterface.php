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

use Generator;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;

/**
 * @since 3.1.0
 */
interface TypeConverterInterface
{

    /**
     * Tries to cast the given value to the given type
     *
     * @param mixed $value
     * @param Type $type
     * @param list<array-key> $path
     * @param array<string, mixed> $context
     *
     * @return Generator<mixed, mixed>
     *
     * @throws InvalidObjectException
     *         Must be thrown if an object associated with the type isn't valid.
     *
     * @throws InvalidValueException
     *         Must be thrown if the value isn't valid.
     *
     * @throws InvalidDataException
     *         Must be thrown if any element of the value isn't valid;
     *         for example, if the type is an array.
     */
    public function castValue($value, Type $type, array $path, array $context): Generator;

    /**
     * Gets the converter's weight
     *
     * @return int<min, max>
     */
    public function getWeight(): int;
}
