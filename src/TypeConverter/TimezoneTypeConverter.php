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

use DateTimeZone;
use Exception;
use Generator;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function is_string;
use function trim;

/**
 * @since 3.1.0
 */
final class TimezoneTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() !== DateTimeZone::class) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        $value = trim($value);

        // As part of the support for HTML forms and other untyped data sources,
        // empty strings should not be used to instantiate timezones;
        // instead, they should be considered as NULL.
        if ($value === '') {
            if ($type->allowsNull()) {
                return yield null;
            }

            throw InvalidValueException::mustNotBeEmpty($path, $value);
        }

        try {
            yield new DateTimeZone($value);
        } catch (Exception $e) {
            throw InvalidValueException::invalidTimezone($path, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 40;
    }
}
