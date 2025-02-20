<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ova vrijednost mora biti navedena.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ova vrijednost ne smije biti prazna.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ova vrijednost mora biti tipa boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Ova vrijednost mora biti tipa integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ova vrijednost mora biti tipa number.',
    ErrorMessage::MUST_BE_STRING => 'Ova vrijednost mora biti tipa string.',
    ErrorMessage::MUST_BE_ARRAY => 'Ova vrijednost mora biti tipa array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ova vrijednost je ograničena na {{ maximum_elements }} elemenata.',
    ErrorMessage::INVALID_CHOICE => 'Ova vrijednost nije valjan izbor; očekivane vrijednosti: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ova vrijednost nije valjan vremenski pečat; očekivani format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ova vrijednost nije valjana vremenska zona.',
    ErrorMessage::INVALID_UID => 'Ova vrijednost nije valjan UID.',
];
