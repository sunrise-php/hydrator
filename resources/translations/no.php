<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Denne verdien må oppgis.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Denne verdien kan ikke være tom.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Denne verdien må være av typen boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Denne verdien må være av typen integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Denne verdien må være av typen number.',
    ErrorMessage::MUST_BE_STRING => 'Denne verdien må være av typen string.',
    ErrorMessage::MUST_BE_ARRAY => 'Denne verdien må være av typen array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Denne verdien er begrenset til {{ maximum_elements }} elementer.',
    ErrorMessage::INVALID_CHOICE => 'Denne verdien er ikke et gyldig valg; forventede verdier: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Denne verdien er ikke et gyldig tidsstempel; forventet format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Denne verdien er ikke en gyldig tidssone.',
    ErrorMessage::INVALID_UID => 'Denne verdien er ikke en gyldig UID.',
];
