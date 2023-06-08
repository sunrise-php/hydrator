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

use BackedEnum;
use Sunrise\Hydrator\Dictionary\ErrorCode;
use RuntimeException;

use function join;
use function sprintf;

/**
 * InvalidValueException
 */
class InvalidValueException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var list<array-key>
     */
    private array $propertyPath;

    /**
     * @var string
     */
    private string $errorCode;

    /**
     * Constructor of the class
     *
     * @param string $message
     * @param string $errorCode
     * @param list<array-key> $propertyPath
     */
    public function __construct(string $message, string $errorCode, array $propertyPath)
    {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->propertyPath = $propertyPath;
    }

    /**
     * @return string
     */
    final public function getPropertyPath(): string
    {
        return join('.', $this->propertyPath);
    }

    /**
     * @return string
     */
    final public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeProvided(array $propertyPath): self
    {
        return new self(
            'This value should be provided.',
            ErrorCode::VALUE_SHOULD_BE_PROVIDED,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldNotBeEmpty(array $propertyPath): self
    {
        return new self(
            'This value should not be empty.',
            ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeBoolean(array $propertyPath): self
    {
        return new self(
            'This value should be of type boolean.',
            ErrorCode::VALUE_SHOULD_BE_BOOLEAN,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeInteger(array $propertyPath): self
    {
        return new self(
            'This value should be of type integer.',
            ErrorCode::VALUE_SHOULD_BE_INTEGER,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeNumber(array $propertyPath): self
    {
        return new self(
            'This value should be of type number.',
            ErrorCode::VALUE_SHOULD_BE_NUMBER,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeString(array $propertyPath): self
    {
        return new self(
            'This value should be of type string.',
            ErrorCode::VALUE_SHOULD_BE_STRING,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function shouldBeArray(array $propertyPath): self
    {
        return new self(
            'This value should be of type array.',
            ErrorCode::VALUE_SHOULD_BE_ARRAY,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param non-empty-string $expectedFormat
     *
     * @return self
     */
    final public static function invalidTimestamp(array $propertyPath, string $expectedFormat): self
    {
        return new self(
            sprintf('This value is not a valid timestamp, expected format: %s.', $expectedFormat),
            ErrorCode::INVALID_TIMESTAMP,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param class-string<BackedEnum> $enumName
     *
     * @return self
     */
    final public static function invalidChoice(array $propertyPath, string $enumName): self
    {
        /** @var list<BackedEnum> $validCases */
        $validCases = $enumName::cases();
        $expectedChoices = [];
        foreach ($validCases as $validCase) {
            $expectedChoices[] = $validCase->value;
        }

        return new self(
            sprintf('This value is not a valid choice, expected choices: %s.', join(', ', $expectedChoices)),
            ErrorCode::INVALID_CHOICE,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param int<1, max> $limit
     *
     * @return self
     */
    final public static function redundantElement(array $propertyPath, int $limit): self
    {
        return new self(
            sprintf('This element is redundant, limit: %d.', $limit),
            ErrorCode::REDUNDANT_ELEMENT,
            $propertyPath,
        );
    }
}
