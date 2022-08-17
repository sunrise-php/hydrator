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
use JsonSerializable;
use RuntimeException;

/**
 * Import functions
 */
use function sprintf;

/**
 * ObjectCollection
 *
 * @template T
 */
abstract class ObjectCollection implements ObjectCollectionInterface, JsonSerializable
{

    /**
     * The collection type
     *
     * @var class-string<T>
     */
    public const T = null;

    /**
     * The collection objects
     *
     * @var array<array-key, T>
     */
    private $objects = [];

    /**
     * Gets the collection type
     *
     * @return class-string<T>
     *
     * @throws RuntimeException
     *         If the called class doesn't contain the T constant.
     */
    final public function getItemClassName() : string
    {
        if (null === static::T) {
            throw new RuntimeException(sprintf(
                'The %s collection must contain the T constant.',
                static::class
            ));
        }

        return static::T;
    }

    /**
     * Checks by the given key if an object exists in the collection
     *
     * @param array-key $key
     *
     * @return bool
     */
    final public function has($key) : bool
    {
        return isset($this->objects[$key]);
    }

    /**
     * Adds the given object to the collection with the given key
     *
     * @param array-key $key
     * @param T $object
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *         If the object cannot be added to the collection.
     */
    final public function add($key, object $object) : void
    {
        $type = $this->getItemClassName();

        if (!($object instanceof $type)) {
            throw new InvalidArgumentException(sprintf(
                'The %s collection can contain the %s objects only.',
                static::class,
                $type
            ));
        }

        $this->objects[$key] = $object;
    }

    /**
     * Gets an object from the collection by the given key
     *
     * @param array-key $key
     *
     * @return T|null
     */
    final public function get($key) : ?object
    {
        return $this->objects[$key] ?? null;
    }

    /**
     * Gets all objects from the collection
     *
     * @return array<array-key, T>
     */
    final public function all() : array
    {
        return $this->objects;
    }

    /**
     * Checks if the collection is empty
     *
     * @return bool
     */
    final public function isEmpty() : bool
    {
        return [] === $this->objects;
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.3.0
     */
    public function jsonSerialize() : array
    {
        return $this->objects;
    }
}
