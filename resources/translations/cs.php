<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Tato hodnota musí být poskytnuta.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Tato hodnota nesmí být prázdná.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Tato hodnota musí být typu boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Tato hodnota musí být typu celého čísla.',
    ErrorMessage::MUST_BE_NUMBER => 'Tato hodnota musí být typu čísla.',
    ErrorMessage::MUST_BE_STRING => 'Tato hodnota musí být typu řetězce.',
    ErrorMessage::MUST_BE_ARRAY => 'Tato hodnota musí být typu pole.',
    ErrorMessage::ARRAY_OVERFLOW => 'Tato hodnota je omezena na {{ maximum_elements }} prvků.',
    ErrorMessage::INVALID_CHOICE => 'Tato hodnota není platnou volbou; očekávané hodnoty: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Tato hodnota není platným časovým razítkem; očekávaný formát: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Tato hodnota není platným časovým pásmem.',
    ErrorMessage::INVALID_UID => 'Tato hodnota není platným UID.',
];
