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

namespace Sunrise\Hydrator\Filter;

/**
 * @since 3.19.0
 */
final class EmptyStringToNull
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function __invoke($value)
    {
        return $value === '' ? null : $value;
    }
}
