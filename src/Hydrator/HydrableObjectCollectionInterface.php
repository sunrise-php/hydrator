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
use IteratorAggregate;

/**
 * HydrableObjectCollectionInterface
 */
interface HydrableObjectCollectionInterface extends IteratorAggregate
{

    /**
     * The type for objects in the collection
     *
     * @var string
     */
    public const T = HydrableObjectInterface::class;

    /**
     * Adds the given object to the collection
     *
     * @param HydrableObjectInterface $object
     *
     * @return void
     */
    public function add(HydrableObjectInterface $object);
}
