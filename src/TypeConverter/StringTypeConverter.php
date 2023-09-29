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

use function is_string;

/**
 * @since 3.1.0
 */
final class StringTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path): Generator
    {
        if ($type->getName() <> BuiltinType::STRING) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::shouldBeString($path);
        }

        yield $value;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 70;
    }
}
