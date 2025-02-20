<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Dieser Wert muss angegeben werden.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Dieser Wert darf nicht leer sein.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Dieser Wert muss vom Typ boolean sein.',
    ErrorMessage::MUST_BE_INTEGER => 'Dieser Wert muss vom Typ integer sein.',
    ErrorMessage::MUST_BE_NUMBER => 'Dieser Wert muss vom Typ number sein.',
    ErrorMessage::MUST_BE_STRING => 'Dieser Wert muss vom Typ string sein.',
    ErrorMessage::MUST_BE_ARRAY => 'Dieser Wert muss vom Typ array sein.',
    ErrorMessage::ARRAY_OVERFLOW => 'Dieser Wert ist auf {{ maximum_elements }} Elemente begrenzt.',
    ErrorMessage::INVALID_CHOICE => 'Dieser Wert ist keine gültige Auswahl; erwartete Werte: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Dieser Wert ist kein gültiger Zeitstempel; erwartetes Format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Dieser Wert ist keine gültige Zeitzone.',
    ErrorMessage::INVALID_UID => 'Dieser Wert ist keine gültige UID.',
];
