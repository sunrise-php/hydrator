<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Tämä arvo on annettava.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Tämä arvo ei saa olla tyhjä.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Tämän arvon on oltava tyyppiä boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Tämän arvon on oltava tyyppiä integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Tämän arvon on oltava tyyppiä number.',
    ErrorMessage::MUST_BE_STRING => 'Tämän arvon tulee olla tyyppiä string.',
    ErrorMessage::MUST_BE_ARRAY => 'Tämän arvon tulee olla tyyppiä array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Tämä arvo on rajattu {{ maximum_elements }} elementtiin.',
    ErrorMessage::INVALID_CHOICE => 'Tämä arvo ei ole kelvollinen valinta; odotetut arvot: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Tämä arvo ei ole kelvollinen aikaleima; odotettu muoto: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Tämä arvo ei ole kelvollinen aikavyöhyke.',
    ErrorMessage::INVALID_UID => 'Tämä arvo ei ole kelvollinen UID.',
];
