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
     * @param object|class-string $object
     * @param array<string, mixed> $data
     *
     * @return object
     *
     * @throws Exception\HydrationException
     *         If the given object cannot be hydrated.
     */
    public function hydrate($object, array $data) : object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param object|class-string $object
     * @param string $json
     * @param int $options
     *
     * @return object
     *
     * @throws Exception\HydrationException
     *         If the given object cannot be hydrated.
     */
    public function hydrateWithJson($object, string $json, int $options) : object;
}
