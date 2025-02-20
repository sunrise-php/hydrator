<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ta wartość musi zostać podana.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ta wartość nie może być pusta.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ta wartość musi być typu boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Ta wartość musi być typu integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ta wartość musi być typu number.',
    ErrorMessage::MUST_BE_STRING => 'Ta wartość musi być typu string.',
    ErrorMessage::MUST_BE_ARRAY => 'Ta wartość musi być typu array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ta wartość jest ograniczona do {{ maximum_elements }} elementów.',
    ErrorMessage::INVALID_CHOICE => 'Ta wartość nie jest prawidłowym wyborem; oczekiwane wartości: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ta wartość nie jest prawidłowym znacznikiem czasu; oczekiwany format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ta wartość nie jest prawidłową strefą czasową.',
    ErrorMessage::INVALID_UID => 'Ta wartość nie jest prawidłowym UID.',
];
