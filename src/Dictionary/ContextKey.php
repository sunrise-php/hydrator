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
 * Hydration context keys
 *
 * @since 3.2.0
 */
final class ContextKey
{
    public const TIMESTAMP_FORMAT = 'timestamp_format';
    public const TIMEZONE = 'timezone';
}
