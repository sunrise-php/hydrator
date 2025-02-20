<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Αυτή η τιμή πρέπει να παρέχεται.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Αυτή η τιμή δεν πρέπει να είναι κενή.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Αυτή η τιμή πρέπει να είναι τύπου boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Αυτή η τιμή πρέπει να είναι τύπου integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Αυτή η τιμή πρέπει να είναι τύπου number.',
    ErrorMessage::MUST_BE_STRING => 'Αυτή η τιμή πρέπει να είναι τύπου string.',
    ErrorMessage::MUST_BE_ARRAY => 'Αυτή η τιμή πρέπει να είναι τύπου array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Αυτή η τιμή περιορίζεται σε {{ maximum_elements }} στοιχεία.',
    ErrorMessage::INVALID_CHOICE => 'Αυτή η τιμή δεν είναι έγκυρη επιλογή· αναμενόμενες τιμές: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Αυτή η τιμή δεν είναι έγκυρη χρονική σήμανση· αναμενόμενη μορφή: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Αυτή η τιμή δεν είναι έγκυρη ζώνη ώρας.',
    ErrorMessage::INVALID_UID => 'Αυτή η τιμή δεν είναι έγκυρο UID.',
];
