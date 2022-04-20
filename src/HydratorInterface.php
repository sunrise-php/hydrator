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
     * @param class-string|object $object
     * @param array|object $data
     *
     * @return object
     */
    public function hydrate($object, $data) : object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string|object $object
     * @param string $json
     * @param ?int $flags
     *
     * @return object
     */
    public function hydrateWithJson($object, string $json, ?int $flags) : object;
}
