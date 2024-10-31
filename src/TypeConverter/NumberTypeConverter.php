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
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function filter_var;
use function is_float;
use function is_int;
use function is_string;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_FLOAT;

/**
 * @since 3.1.0
 */
final class NumberTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() !== BuiltinType::FLOAT) {
            return;
        }

        if (is_int($value)) {
            return yield (float) $value;
        }

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // empty strings should not be cast to the number type;
            // instead, they should be considered as NULL.
            if (trim($value) === '') {
                if ($type->allowsNull()) {
                    return yield null;
                }

                throw InvalidValueException::mustNotBeEmpty($path, $value);
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L342
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        }

        if (!is_float($value)) {
            throw InvalidValueException::mustBeNumber($path, $value);
        }

        yield $value;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 80;
    }
}
