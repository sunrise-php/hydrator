<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Denne værdi skal angives.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Denne værdi må ikke være tom.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Denne værdi skal være af typen boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Denne værdi skal være af typen heltal.',
    ErrorMessage::MUST_BE_NUMBER => 'Denne værdi skal være af typen nummer.',
    ErrorMessage::MUST_BE_STRING => 'Denne værdi skal være af typen streng.',
    ErrorMessage::MUST_BE_ARRAY => 'Denne værdi skal være af typen array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Denne værdi er begrænset til {{ maximum_elements }} elementer.',
    ErrorMessage::INVALID_CHOICE => 'Denne værdi er ikke et gyldigt valg; forventede værdier: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Denne værdi er ikke et gyldigt tidsstempel; forventet format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Denne værdi er ikke en gyldig tidszone.',
    ErrorMessage::INVALID_UID => 'Denne værdi er ikke en gyldig UID.',
];
