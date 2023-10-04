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
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function is_string;
use function trim;

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
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() <> UuidInterface::class) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::mustBeString($path);
        }

        $value = trim($value);

        // As part of the support for HTML forms and other untyped data sources,
        // empty strings should not be used to instantiate uids;
        // instead, they should be considered as NULL.
        if ($value === '') {
            if ($type->allowsNull()) {
                return yield null;
            }

            throw InvalidValueException::mustNotBeEmpty($path);
        }

        try {
            yield Uuid::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::invalidUid($path);
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
