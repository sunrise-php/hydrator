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
     * @param class-string<T>|T $object
     * @param array|object $data
     *
     * @return T
     *
     * @throws Exception\HydrationException
     *         If the object cannot be hydrated.
     *
     * @throws \InvalidArgumentException
     *         If the data isn't valid.
     *
     * @template T
     */
    public function hydrate($object, $data) : object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string<T>|T $object
     * @param string $json
     * @param ?int $flags
     *
     * @return T
     *
     * @throws Exception\HydrationException
     *         If the object cannot be hydrated.
     *
     * @throws \InvalidArgumentException
     *         If the JSON cannot be decoded.
     *
     * @template T
     */
    public function hydrateWithJson($object, string $json, ?int $flags) : object;
}
