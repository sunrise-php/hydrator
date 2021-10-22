<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2021, Anatoly Fenric
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

namespace Sunrise\Hydrator\Annotation;

/**
 * Import classes
 */
use Attribute;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *   @Attribute("value", type="string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Alias
{

    /**
     * The attribute value
     *
     * @var string
     */
    public $value;

    /**
     * Constructor of the class
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
