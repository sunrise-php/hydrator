<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Aquest valor s\'ha de proporcionar.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Aquest valor no ha de ser buit.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Aquest valor ha de ser del tipus boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Aquest valor ha de ser del tipus integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Aquest valor ha de ser de tipus number.',
    ErrorMessage::MUST_BE_STRING => 'Aquest valor ha de ser de tipus string.',
    ErrorMessage::MUST_BE_ARRAY => 'Aquest valor ha de ser del tipus array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Aquest valor es limita a {{ maximum_elements }} elements.',
    ErrorMessage::INVALID_CHOICE => 'Aquest valor no és una opció vàlida; valors esperats: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Aquest valor no és una marca de temps vàlida; format esperat: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Aquest valor no és una zona horària vàlida.',
    ErrorMessage::INVALID_UID => 'Aquest valor no és un UID vàlid.',
];
