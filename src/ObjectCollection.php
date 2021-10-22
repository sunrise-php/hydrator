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
use RuntimeException;

/**
 * Import functions
 */
use function sprintf;

/**
 * ObjectCollection
 */
abstract class ObjectCollection implements ObjectCollectionInterface
{

    /**
     * The type of objects in the collection
     *
     * @var class-string<T>
     *
     * @template T
     */
    public const T = null;

    /**
     * The collection objects
     *
     * @var array<int|string, T>
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
                'The <%s> collection must contain the <T> constant.',
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
                'The <%s> collection must contain the <%s> objects only.',
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
}
