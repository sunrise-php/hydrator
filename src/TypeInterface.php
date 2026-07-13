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

namespace Sunrise\Hydrator;

/**
 * @since 3.20.0
 */
interface TypeInterface
{
    /**
     * @return mixed
     */
    public function getHolder();

    public function getName(): string;

    public function allowsNull(): bool;
}
