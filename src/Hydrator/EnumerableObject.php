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
 * EnumerableObject
 */
abstract class EnumerableObject implements EnumerableObjectInterface
{

    /**
     * The enum value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructor of the class
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
