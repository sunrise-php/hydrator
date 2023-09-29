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

use Generator;
use ReflectionProperty;

/**
 * @since 3.1.0
 */
interface AnnotationReaderInterface
{

    /**
     * Gets annotations from the given target by the given annotation name
     *
     * @param ReflectionProperty $target
     * @param class-string<T> $name
     *
     * @return Generator<mixed, T>
     *
     * @template T of object
     */
    public function getAnnotations(ReflectionProperty $target, string $name): Generator;
}
