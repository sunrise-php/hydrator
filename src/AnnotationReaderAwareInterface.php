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

/**
 * @since 3.1.0
 */
interface AnnotationReaderAwareInterface
{

    /**
     * Sets the given annotation reader
     *
     * @param AnnotationReaderInterface $annotationReader
     *
     * @return void
     */
    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void;
}
