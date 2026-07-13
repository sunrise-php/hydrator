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

use Generator;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\TypeConverterInterface;
use Sunrise\Hydrator\TypeInterface;

/**
 * @since 3.1.0
 */
final class IntegerTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, TypeInterface $type, array $path, array $context): Generator
    {
        if ($type->getName() !== BuiltinType::INT) {
            return;
        }

        if (\is_string($value)) {
            $value = \trim($value);

            if ($value === '') {
                if ($type->allowsNull()) {
                    yield null;
                    return;
                }

                throw InvalidValueException::mustNotBeEmpty($path, $value);
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
            $value = \filter_var($value, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE);
        }

        if (!\is_int($value)) {
            throw InvalidValueException::mustBeInteger($path, $value);
        }

        yield $value;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 90;
    }
}
