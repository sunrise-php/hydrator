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
 * Import classes
 */
use InvalidArgumentException;

/**
 * ObjectCollectionInterface
 */
interface ObjectCollectionInterface
{

    /**
     * Gets the type of objects in the collection
     *
     * @return class-string
     */
    public function getItemClassName() : string;

    /**
     * Adds the given object to the collection by the given key
     *
     * @param int|string $key
     * @param object $object
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *         If the given object cannot be added to the collection.
     */
    public function add($key, object $object) : void;

    /**
     * Gets an object from the collection by the given key
     *
     * @param int|string $key
     *
     * @return object|null
     */
    public function get($key) : ?object;

    /**
     * Gets all objects of the collection
     *
     * @return array<int|string, object>
     */
    public function all() : array;
}
