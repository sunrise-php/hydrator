<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ta vrednost mora biti podana.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ta vrednost ne sme biti prazna.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ta vrednost mora biti tipa boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Ta vrednost mora biti tipa integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ta vrednost mora biti tipa number.',
    ErrorMessage::MUST_BE_STRING => 'Ta vrednost mora biti tipa string.',
    ErrorMessage::MUST_BE_ARRAY => 'Ta vrednost mora biti tipa array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ta vrednost je omejena na {{ maximum_elements }} elementov.',
    ErrorMessage::INVALID_CHOICE => 'Ta vrednost ni veljavna izbira; pričakovane vrednosti: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ta vrednost ni veljaven časovni žig; pričakovani format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ta vrednost ni veljavna časovna cona.',
    ErrorMessage::INVALID_UID => 'Ta vrednost ni veljaven UID.',
];
