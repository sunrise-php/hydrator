<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2021, Anatoly Nekhay
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

declare(strict_types=1);

namespace Sunrise\Hydrator\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *     @Attribute("value", type="array", required=true),
 * })
 *
 * @since 3.2.0
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Context
{

    /**
     * The attribute value
     *
     * @var array<non-empty-string, mixed>
     *
     * @readonly
     */
    public array $value;

    /**
     * Constructor of the class
     *
     * @param array<non-empty-string, mixed> $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}
