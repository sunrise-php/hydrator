<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ова вредност мора бити обезбеђена.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ова вредност не сме бити празна.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ова вредност мора бити типа boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Ова вредност мора бити типа integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Ова вредност мора бити типа number.',
    ErrorMessage::MUST_BE_STRING => 'Ова вредност мора бити типа string.',
    ErrorMessage::MUST_BE_ARRAY => 'Ова вредност мора бити типа array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ова вредност је ограничена на {{ maximum_elements }} елемената.',
    ErrorMessage::INVALID_CHOICE => 'Ова вредност није важећи избор; очекиване вредности: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ова вредност није важећи временски печат; очекивани формат: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ова вредност није важећа временска зона.',
    ErrorMessage::INVALID_UID => 'Ова вредност није важећи UID.',
];
