<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Această valoare trebuie furnizată.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Această valoare nu trebuie să fie goală.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Această valoare trebuie să fie de tip boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Această valoare trebuie să fie de tip integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Această valoare trebuie să fie de tipul number.',
    ErrorMessage::MUST_BE_STRING => 'Această valoare trebuie să fie de tipul string.',
    ErrorMessage::MUST_BE_ARRAY => 'Această valoare trebuie să fie de tip array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Această valoare este limitată la {{ maximum_elements }} elemente.',
    ErrorMessage::INVALID_CHOICE => 'Această valoare nu este o alegere validă; valorile așteptate: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Această valoare nu este un marcaj temporal valid; formatul așteptat: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Această valoare nu este un fus orar valid.',
    ErrorMessage::INVALID_UID => 'Această valoare nu este un UID valid.',
];
