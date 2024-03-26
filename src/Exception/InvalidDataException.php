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

namespace Sunrise\Hydrator\Exception;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use RuntimeException;

/**
 * InvalidDataException
 *
 * @since 3.0.0
 */
class InvalidDataException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var list<InvalidValueException>
     */
    private array $exceptions;

    /**
     * Constructor of the class
     *
     * @param string $message
     * @param list<InvalidValueException> $exceptions
     */
    public function __construct(string $message, array $exceptions = [])
    {
        parent::__construct($message);

        $this->exceptions = $exceptions;
    }

    /**
     * @return list<InvalidValueException>
     */
    final public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    final public function getViolations(): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();
        foreach ($this->exceptions as $exception) {
            $violations->add($exception->getViolation());
        }

        return $violations;
    }
}
