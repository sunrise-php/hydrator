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

namespace Sunrise\Hydrator\Dictionary;

/**
 * Built-in types
 *
 * @since 3.1.0
 */
final class BuiltinType
{
    public const MIXED = 'mixed';
    public const BOOL = 'bool';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const ARRAY = 'array';
}
