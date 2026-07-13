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

namespace Sunrise\Hydrator\Annotation;

use Attribute;

/**
 * @since 3.19.0
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Filter
{
    /**
     * @var callable(mixed):mixed
     *
     * @readonly
     */
    public $value;

    /**
     * @param callable(mixed):mixed $value
     */
    public function __construct(callable $value)
    {
        $this->value = $value;
    }
}
