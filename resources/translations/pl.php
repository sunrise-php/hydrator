<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'To pole musi być dostarczone.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'To pole nie może być puste.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ta wartość musi być typu boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'To pole musi być typu integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ta wartość musi być typu number.',
    ErrorMessage::MUST_BE_STRING => 'To pole musi być typu string.',
    ErrorMessage::MUST_BE_ARRAY => 'To pole musi być typu array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ta wartość jest ograniczona do {{ maximum_elements }} elementów.',
    ErrorMessage::INVALID_CHOICE => 'Ta wartość nie jest prawidłowym wyborem; oczekiwane wartości: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ta wartość nie jest prawidłowym znacznikiem czasu; oczekiwany format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'To nie jest prawidłowa strefa czasowa.',
    ErrorMessage::INVALID_UID => 'Ta wartość nie jest prawidłowym UID.',
];
