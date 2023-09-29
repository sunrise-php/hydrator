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
 *     @Attribute("limit", type="integer", required=false),
 * })
 *
 * @final See the {@see Relationship} class.
 *
 * @since 3.1.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Subtype
{

    /**
     * @var non-empty-string
     *
     * @readonly
     */
    public string $name;

    /**
     * @var int<0, max>|null
     *
     * @readonly
     */
    public ?int $limit;

    /**
     * Constructor of the class
     *
     * @param non-empty-string $name
     * @param int<0, max>|null $limit
     */
    public function __construct(string $name, ?int $limit = null)
    {
        $this->name = $name;
        $this->limit = $limit;
    }
}
