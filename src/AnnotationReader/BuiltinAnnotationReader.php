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
use LogicException;
use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use Sunrise\Hydrator\AnnotationReaderInterface;

use function sprintf;

use const PHP_MAJOR_VERSION;

/**
 * @since 3.1.0
 */
final class BuiltinAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @throws LogicException If the PHP version less than 8.0.
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (PHP_MAJOR_VERSION < 8) {
            throw new LogicException(sprintf(
                'The annotation reader {%s} requires PHP version greater than or equal to 8.0.',
                __CLASS__,
            ));
        } // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritDoc
     */
    public function getAnnotations(string $name, $holder): Generator
    {
        // @codeCoverageIgnoreStart
        if (PHP_MAJOR_VERSION < 8) {
            return;
        } // @codeCoverageIgnoreEnd

        if (! $holder instanceof ReflectionProperty &&
            ! $holder instanceof ReflectionParameter) {
            return;
        }

        $attributes = $holder->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
