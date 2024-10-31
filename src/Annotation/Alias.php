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
 *     @Attribute("value", type="string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Alias
{
    /**
     * @var non-empty-string
     *
     * @readonly
     */
    public string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
