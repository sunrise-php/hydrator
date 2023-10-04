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

namespace Sunrise\Hydrator\AnnotationReader;

use Generator;
use Sunrise\Hydrator\AnnotationReaderInterface;

/**
 * @since 3.2.0
 */
final class NullAnnotationReader implements AnnotationReaderInterface
{

    /**
     * @inheritDoc
     *
     * @psalm-suppress InvalidReturnType
     */
    public function getAnnotations(string $name, $holder): Generator
    {
        yield from [];
    }
}
