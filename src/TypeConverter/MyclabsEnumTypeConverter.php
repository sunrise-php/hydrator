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
use MyCLabs\Enum\Enum;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;
use UnexpectedValueException;

use function is_string;
use function is_subclass_of;
use function trim;

/**
 * @link https://github.com/myclabs/php-enum
 *
 * @since 3.2.0
 */
final class MyclabsEnumTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        $enumName = $type->getName();
        if (!is_subclass_of($enumName, Enum::class)) {
            return;
        }

        if (is_string($value)) {
            $value = trim($value);

            // As part of the support for HTML forms and other untyped data sources,
            // empty strings should not be used to instantiate enumerations;
            // instead, they should be considered as NULL.
            if ($value === '') {
                if ($type->allowsNull()) {
                    return yield null;
                }

                throw InvalidValueException::mustNotBeEmpty($path);
            }
        }

        try {
            yield $enumName::from($value);
        } catch (UnexpectedValueException $e) {
            $expectedValues = [];
            foreach ($enumName::values() as $case) {
                /** @var int|string */
                $expectedValues[] = $case->getValue();
            }

            throw InvalidValueException::invalidChoice($path, $expectedValues);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 60;
    }
}
