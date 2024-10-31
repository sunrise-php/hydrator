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

/**
 * @since 3.1.0
 */
interface AnnotationReaderInterface
{
    /**
     * Gets annotations by the given name from the given holder
     *
     * @param class-string<T> $name
     * @param mixed $holder
     *
     * @return Generator<mixed, T>
     *
     * @template T of object
     */
    public function getAnnotations(string $name, $holder): Generator;
}
