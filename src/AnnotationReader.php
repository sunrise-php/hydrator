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
use LogicException;
use ReflectionAttribute;
use ReflectionProperty;

use function sprintf;

use const PHP_MAJOR_VERSION;

/**
 * @since 3.1.0
 */
final class AnnotationReader implements AnnotationReaderInterface
{

    /**
     * Constructor of the class
     *
     * @throws LogicException If PHP version less than 8.0.
     */
    public function __construct()
    {
        if (PHP_MAJOR_VERSION < 8) {
            throw new LogicException(sprintf(
                'The annotation reader {%s} requires PHP version greater than or equal to 8.0.',
                __CLASS__,
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function getAnnotations(ReflectionProperty $target, string $name): Generator
    {
        if (PHP_MAJOR_VERSION < 8) {
            return;
        }

        $attributes = $target->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
