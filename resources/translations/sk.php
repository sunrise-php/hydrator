<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Táto hodnota musí byť zadaná.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Táto hodnota nesmie byť prázdna.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Táto hodnota musí byť typu boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Táto hodnota musí byť typu integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Táto hodnota musí byť typu number.',
    ErrorMessage::MUST_BE_STRING => 'Táto hodnota musí byť typu string.',
    ErrorMessage::MUST_BE_ARRAY => 'Táto hodnota musí byť typu array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Táto hodnota je obmedzená na {{ maximum_elements }} prvkov.',
    ErrorMessage::INVALID_CHOICE => 'Táto hodnota nie je platnou voľbou; očakávané hodnoty: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Táto hodnota nie je platný časový údaj; očakávaný formát: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Táto hodnota nie je platné časové pásmo.',
    ErrorMessage::INVALID_UID => 'Táto hodnota nie je platný UID.',
];
