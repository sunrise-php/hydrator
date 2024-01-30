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
 * Hydration error messages
 *
 * @since 3.7.0
 */
final class ErrorMessage
{
    public const MUST_BE_PROVIDED = 'This value must be provided.';
    public const MUST_NOT_BE_EMPTY = 'This value must not be empty.';
    public const MUST_BE_BOOLEAN = 'This value must be of type boolean.';
    public const MUST_BE_INTEGER = 'This value must be of type integer.';
    public const MUST_BE_NUMBER = 'This value must be of type number.';
    public const MUST_BE_STRING = 'This value must be of type string.';
    public const MUST_BE_ARRAY = 'This value must be of type array.';
    public const ARRAY_OVERFLOW = 'This value is limited to {{ maximum_elements }} elements.';
    public const INVALID_CHOICE = 'This value is not a valid choice; expected values: {{ expected_values }}.';
    public const INVALID_TIMESTAMP = 'This value is not a valid timestamp; expected format: {{ expected_format }}.';
    public const INVALID_TIMEZONE = 'This value is not a valid timezone.';
    public const INVALID_UID = 'This value is not a valid UID.';
}
