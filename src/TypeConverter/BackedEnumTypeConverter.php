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

use BackedEnum;
use Generator;
use ReflectionEnum;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;
use ValueError;

/**
 * @since 3.1.0
 */
final class BackedEnumTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        // @codeCoverageIgnoreStart
        if (\PHP_VERSION_ID < 80100) {
            return;
        } // @codeCoverageIgnoreEnd

        $enumName = $type->getName();
        if (!\is_subclass_of($enumName, BackedEnum::class)) {
            return;
        }

        $enumTypeName = (string) (new ReflectionEnum($enumName))->getBackingType();

        if (\is_string($value)) {
            $value = \trim($value);

            if ($value === '') {
                if ($type->allowsNull()) {
                    yield null;
                    return;
                }

                throw InvalidValueException::mustNotBeEmpty($path, $value);
            }

            if ($enumTypeName === BuiltinType::INT) {
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
                $value = \filter_var($value, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE);
            }
        }

        if ($enumTypeName === BuiltinType::INT && !\is_int($value)) {
            throw InvalidValueException::mustBeInteger($path, $value);
        }
        if ($enumTypeName === BuiltinType::STRING && !\is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        /** @var int|string $value */

        try {
            yield $enumName::from($value);
        } catch (ValueError $e) {
            $expectedValues = [];
            foreach ($enumName::cases() as $case) {
                $expectedValues[] = $case->value;
            }

            throw InvalidValueException::invalidChoice($path, $expectedValues, $value);
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
