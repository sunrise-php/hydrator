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

namespace Sunrise\Hydrator;

/**
 * @since 3.1.0
 */
interface HydratorAwareInterface
{

    /**
     * Sets the given hydrator to the object
     *
     * @param HydratorInterface $hydrator
     *
     * @return void
     */
    public function setHydrator(HydratorInterface $hydrator): void;
}
