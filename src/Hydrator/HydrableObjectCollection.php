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
use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * Import functions
 */
use function get_called_class;
use function sprintf;

/**
 * HydrableObjectCollection
 */
class HydrableObjectCollection implements HydrableObjectCollectionInterface
{

    /**
     * The collection objects
     *
     * @var HydrableObjectInterface[]
     */
    private array $objects = [];

    /**
     * {@inheritDoc}
     */
    final public function add(HydrableObjectInterface $object)
    {
        $type = static::T;

        if (!($object instanceof $type)) {
            throw new InvalidArgumentException(sprintf(
                'The <%s> collection must contain only the <%s> objects.',
                get_called_class(),
                $type,
            ));
        }

        $this->objects[] = $object;
    }

    /**
     * {@inheritDoc}
     */
    final public function getIterator() : Traversable
    {
        return new ArrayIterator($this->objects);
    }
}
