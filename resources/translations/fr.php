<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Cette valeur doit être fournie.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Cette valeur ne doit pas être vide.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Cette valeur doit être de type boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Cette valeur doit être de type integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Cette valeur doit être de type number.',
    ErrorMessage::MUST_BE_STRING => 'Cette valeur doit être de type string.',
    ErrorMessage::MUST_BE_ARRAY => 'Cette valeur doit être de type array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Cette valeur est limitée à {{ maximum_elements }} éléments.',
    ErrorMessage::INVALID_CHOICE => 'Cette valeur n\'est pas un choix valide ; valeurs attendues : {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Cette valeur n\'est pas un timestamp valide ; format attendu : {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Cette valeur n\'est pas un fuseau horaire valide.',
    ErrorMessage::INVALID_UID => 'Cette valeur n\'est pas un UID valide.',
];
