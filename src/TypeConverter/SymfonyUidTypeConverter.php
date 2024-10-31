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
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;
use Symfony\Component\Uid\AbstractUid;

use function is_string;
use function is_subclass_of;
use function trim;

/**
 * @link https://github.com/symfony/uid
 *
 * @since 3.1.0
 */
final class SymfonyUidTypeConverter implements TypeConverterInterface
{
    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        $className = $type->getName();
        if (!is_subclass_of($className, AbstractUid::class)) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        $value = trim($value);

        // As part of the support for HTML forms and other untyped data sources,
        // empty strings should not be used to instantiate uids;
        // instead, they should be considered as NULL.
        if ($value === '') {
            if ($type->allowsNull()) {
                return yield null;
            }

            throw InvalidValueException::mustNotBeEmpty($path, $value);
        }

        try {
            yield $className::fromString($value);
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
