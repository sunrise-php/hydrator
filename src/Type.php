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

namespace Sunrise\Hydrator;

use ReflectionProperty;

/**
 * @since 3.1.0
 */
final class Type
{

    /**
     * The type holder
     *
     * @var ReflectionProperty
     */
    private ReflectionProperty $holder;

    /**
     * The type name
     *
     * @var string
     */
    private string $name;

    /**
     * Indicates whether the type allows null
     *
     * @var bool
     */
    private bool $allowsNull;

    /**
     * Constructor of the class
     *
     * @param ReflectionProperty $holder
     * @param string $name
     * @param bool $allowsNull
     */
    public function __construct(ReflectionProperty $holder, string $name, bool $allowsNull)
    {
        $this->holder = $holder;
        $this->name = $name;
        $this->allowsNull = $allowsNull;
    }

    /**
     * Gets the type holder
     *
     * @return ReflectionProperty
     */
    public function getHolder(): ReflectionProperty
    {
        return $this->holder;
    }

    /**
     * Gets the type name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Checks if the type allows null
     *
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}
