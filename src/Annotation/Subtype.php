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
 * @since 3.1.0
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Subtype
{
    /**
     * @var mixed
     *
     * @internal
     */
    public $holder = null;

    /**
     * @var non-empty-string
     *
     * @readonly
     */
    public string $name;

    /**
     * @var bool
     *
     * @readonly
     */
    public bool $allowsNull;

    /**
     * @var int<0, max>|null
     *
     * @readonly
     */
    public ?int $limit;

    /**
     * @param non-empty-string $name
     * @param bool $allowsNull
     * @param int<0, max>|null $limit
     */
    public function __construct(string $name, bool $allowsNull = false, ?int $limit = null)
    {
        $this->name = $name;
        $this->allowsNull = $allowsNull;
        $this->limit = $limit;
    }
}
