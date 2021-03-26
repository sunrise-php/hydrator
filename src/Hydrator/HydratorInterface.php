<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2021, Anatoly Fenric
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

namespace Sunrise\Hydrator;

/**
 * HydratorInterface
 */
interface HydratorInterface
{

    /**
     * Hydrates the given object with the given data
     *
     * @param HydrableObjectInterface $object
     * @param array $data
     *
     * @return void
     *
     * @throws Exception\HydrationException
     *         If any error occurred during the hydration process.
     */
    public function hydrate(HydrableObjectInterface $object, array $data);
}
