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
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\TypeConverterInterface;
use Sunrise\Hydrator\TypeInterface;

/**
 * @link https://github.com/ramsey/uuid
 *
 * @since 3.2.0
 */
final class RamseyUuidTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, TypeInterface $type, array $path, array $context): Generator
    {
        if ($type->getName() !== UuidInterface::class) {
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
            yield Uuid::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::invalidUid($path, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 30;
    }
}
