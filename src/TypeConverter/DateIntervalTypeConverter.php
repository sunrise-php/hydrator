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

use DateInterval;
use Generator;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;
use Throwable;

/**
 * @since 3.20.0
 */
final class DateIntervalTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() !== DateInterval::class) {
            return;
        }

        if (!\is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        $value = \trim($value);

        if ($value === '') {
            if ($type->allowsNull()) {
                yield null;
                return;
            }

            throw InvalidValueException::mustNotBeEmpty($path, $value);
        }

        try {
            yield new DateInterval($value);
        } catch (Throwable $e) {
            throw InvalidValueException::invalidDateInterval($path, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 45;
    }
}
