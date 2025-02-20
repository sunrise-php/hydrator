<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'יש לספק ערך זה.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'ערך זה לא יכול להיות ריק.',
    ErrorMessage::MUST_BE_BOOLEAN => 'ערך זה חייב להיות מסוג boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'ערך זה חייב להיות מסוג integer.',
    ErrorMessage::MUST_BE_NUMBER => 'ערך זה חייב להיות מסוג number.',
    ErrorMessage::MUST_BE_STRING => 'ערך זה חייב להיות מסוג string.',
    ErrorMessage::MUST_BE_ARRAY => 'ערך זה חייב להיות מסוג array.',
    ErrorMessage::ARRAY_OVERFLOW => 'ערך זה מוגבל ל-{{ maximum_elements }} רכיבים.',
    ErrorMessage::INVALID_CHOICE => 'ערך זה אינו אפשרות תקפה; ערכים צפויים: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'ערך זה אינו תאריך ושעה תקפים; פורמט צפוי: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'ערך זה אינו אזור זמן תקף.',
    ErrorMessage::INVALID_UID => 'ערך זה אינו UID תקף.',
];
