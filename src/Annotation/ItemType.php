<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2021, Anatolii Nekhai
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

declare(strict_types=1);

namespace Sunrise\Hydrator\Annotation;

use Attribute;
use Sunrise\Hydrator\TypeInterface;

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
 * @since 3.20.0
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ItemType implements TypeInterface
{
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
     * @var mixed
     */
    public $holder = null;

    /**
     * @param non-empty-string $name
     * @param bool $allowsNull
     * @param int<0, max>|null $limit
     */
    public function __construct(
        string $name,
        bool $allowsNull = false,
        ?int $limit = null
    ) {
        $this->name = $name;
        $this->allowsNull = $allowsNull;
        $this->limit = $limit;
    }

    /**
     * @inheritDoc
     */
    public function getHolder()
    {
        return $this->holder;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}
