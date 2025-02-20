<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Deze waarde moet worden opgegeven.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Deze waarde mag niet leeg zijn.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Deze waarde moet van het type boolean zijn.',
    ErrorMessage::MUST_BE_INTEGER => 'Deze waarde moet van het type integer zijn.',
    ErrorMessage::MUST_BE_NUMBER => 'Deze waarde moet van het type number zijn.',
    ErrorMessage::MUST_BE_STRING => 'Deze waarde moet van het type string zijn.',
    ErrorMessage::MUST_BE_ARRAY => 'Deze waarde moet van het type array zijn.',
    ErrorMessage::ARRAY_OVERFLOW => 'Deze waarde is beperkt tot {{ maximum_elements }} elementen.',
    ErrorMessage::INVALID_CHOICE => 'Deze waarde is geen geldige keuze; verwachte waarden: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Deze waarde is geen geldige tijdstempel; verwacht formaat: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Deze waarde is geen geldige tijdzone.',
    ErrorMessage::INVALID_UID => 'Deze waarde is geen geldige UID.',
];
