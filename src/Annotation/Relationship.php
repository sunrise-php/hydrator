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
 *     @Attribute("name", type="string", required=true),
 *     @Attribute("allowsNull", type="boolean", required=false),
 *     @Attribute("limit", type="integer", required=false),
 * })
 *
 * @since 3.0.0
 *
 * @deprecated 3.1.0 Use the {@see Subtype} annotation.
 *
 * @psalm-suppress InvalidExtendClass
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Relationship extends Subtype
{
}
