<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Tämä arvo on annettava.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Tämä arvo ei saa olla tyhjä.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Tämän arvon on oltava tyypiltään boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Tämän arvon on oltava tyypiltään integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Tämän arvon on oltava tyypiltään number.',
    ErrorMessage::MUST_BE_STRING => 'Tämän arvon on oltava tyypiltään string.',
    ErrorMessage::MUST_BE_ARRAY => 'Tämän arvon on oltava tyypiltään array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Tämä arvo on rajoitettu {{ maximum_elements }} elementtiin.',
    ErrorMessage::INVALID_CHOICE => 'Tämä arvo ei ole kelvollinen valinta; odotetut arvot: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Tämä arvo ei ole kelvollinen aikaleima; odotettu muoto: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Tämä arvo ei ole kelvollinen aikavyöhyke.',
    ErrorMessage::INVALID_UID => 'Tämä arvo ei ole kelvollinen UID.',
];
