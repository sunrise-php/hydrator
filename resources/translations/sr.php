<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ova vrednost mora biti navedena.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ova vrednost ne sme biti prazna.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ova vrednost mora biti tipa boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Ova vrednost mora biti tipa integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ova vrednost mora biti tipa number.',
    ErrorMessage::MUST_BE_STRING => 'Ova vrednost mora biti tipa string.',
    ErrorMessage::MUST_BE_ARRAY => 'Ova vrednost mora biti tipa array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ova vrednost je ograničena na {{ maximum_elements }} elemenata.',
    ErrorMessage::INVALID_CHOICE => 'Ova vrednost nije važeći izbor; očekivane vrednosti: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ova vrednost nije ispravna vremenska oznaka; očekivani format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ova vrednost nije važeća vremenska zona.',
    ErrorMessage::INVALID_UID => 'Ova vrednost nije važeći UID.',
];
