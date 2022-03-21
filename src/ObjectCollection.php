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
 */
abstract class ObjectCollection implements ObjectCollectionInterface, JsonSerializable
{

    /**
     * The type of objects in the collection
     *
     * @var class-string
     */
    public const T = null;

    /**
     * The collection objects
     *
     * @var array<array-key, object>
     */
    private $objects = [];

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    final public function get($key) : ?object
    {
        return $this->objects[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    final public function all() : array
    {
        return $this->objects;
    }

    /**
     * {@inheritdoc}
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
