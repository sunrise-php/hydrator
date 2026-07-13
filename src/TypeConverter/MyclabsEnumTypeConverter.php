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
use MyCLabs\Enum\Enum;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\TypeConverterInterface;
use Sunrise\Hydrator\TypeInterface;
use UnexpectedValueException;

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
    public function castValue($value, TypeInterface $type, array $path, array $context): Generator
    {
        $enumName = $type->getName();
        if (!\is_subclass_of($enumName, Enum::class)) {
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
        }

        try {
            yield $enumName::from($value);
        } catch (UnexpectedValueException $e) {
            $expectedValues = [];
            foreach ($enumName::values() as $case) {
                /** @var int|string $value */
                $value = $case->getValue();
                $expectedValues[] = $value;
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
