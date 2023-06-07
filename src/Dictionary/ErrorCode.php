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
 * Hydration error codes
 *
 * @since 3.0.0
 */
final class ErrorCode
{
    public const VALUE_SHOULD_BE_PROVIDED = 'ed27fc47-975a-4369-b311-ebfe47a71d9f';
    public const VALUE_SHOULD_NOT_BE_EMPTY = '9d423958-414d-45e3-bd1c-a100a890b5d5';
    public const VALUE_SHOULD_BE_BOOLEAN = '97ee8405-e803-4cf3-bf66-44b81d8627dc';
    public const VALUE_SHOULD_BE_INTEGER = '269f64e0-c1d2-449e-98fc-06e0d4ba05ca';
    public const VALUE_SHOULD_BE_NUMBER = 'b30f9ed7-8d8d-451e-9a04-86794c2a0720';
    public const VALUE_SHOULD_BE_STRING = 'c84a6c6c-19d1-49a2-a74e-daea88eeea52';
    public const VALUE_SHOULD_BE_ARRAY = 'b171342e-de67-409b-9edc-8ccbdf36f2af';
    public const INVALID_TIMESTAMP = 'b0a14918-9e20-470d-8ba3-3d85953ddbce';
    public const INVALID_CHOICE = 'e5bd8e3f-60a0-4066-b89b-ef5a186f2836';
    public const REDUNDANT_ELEMENT = '917e1646-b996-4f34-a4f2-1c075bb6e715';
}
