<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Aquest valor ha de ser proporcionat.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Aquest valor no ha de ser buit.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Aquest valor ha de ser de tipus booleà.',
    ErrorMessage::MUST_BE_INTEGER => 'Aquest valor ha de ser de tipus enter.',
    ErrorMessage::MUST_BE_NUMBER => 'Aquest valor ha de ser de tipus número.',
    ErrorMessage::MUST_BE_STRING => 'Aquest valor ha de ser de tipus cadena.',
    ErrorMessage::MUST_BE_ARRAY => 'Aquest valor ha de ser de tipus matriu.',
    ErrorMessage::ARRAY_OVERFLOW => 'Aquest valor està limitat a {{ maximum_elements }} elements.',
    ErrorMessage::INVALID_CHOICE => 'Aquest valor no és una opció vàlida; valors esperats: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Aquest valor no és un segell de temps vàlid; format esperat: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Aquest valor no és una zona horària vàlida.',
    ErrorMessage::INVALID_UID => 'Aquest valor no és un UID vàlid.',
];
