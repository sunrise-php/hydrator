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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Generator;
use LogicException;
use ReflectionProperty;
use Sunrise\Hydrator\AnnotationReaderInterface;

use function class_exists;
use function sprintf;

/**
 * @link https://github.com/doctrine/annotations
 *
 * @since 3.1.0
 */
final class DoctrineAnnotationReader implements AnnotationReaderInterface
{

    /**
     * @var Reader
     */
    private Reader $reader;

    /**
     * Constructor of the class
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Creates a new instance of the class with the doctrine's default annotation reader
     *
     * @return self
     *
     * @throws LogicException If the doctrine/annotations package isn't installed on the server.
     */
    public static function default(): self
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(AnnotationReader::class)) {
            throw new LogicException(sprintf(
                'The annotation reader {%s} requires the doctrine/annotations package, ' .
                'run the command `composer require doctrine/annotations` to resolve it.',
                __CLASS__,
            ));
        } // @codeCoverageIgnoreEnd

        return new self(new AnnotationReader());
    }

    /**
     * @inheritDoc
     */
    public function getAnnotations(string $name, $holder): Generator
    {
        if ($holder instanceof ReflectionProperty) {
            $annotations = $this->reader->getPropertyAnnotations($holder);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof $name) {
                    yield $annotation;
                }
            }
        }
    }
}
