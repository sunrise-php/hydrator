<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Bu değer sağlanmalıdır.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Bu değer boş olmamalıdır.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Bu değer boolean türünde olmalıdır.',
    ErrorMessage::MUST_BE_INTEGER => 'Bu değer integer türünde olmalıdır.',
    ErrorMessage::MUST_BE_NUMBER => 'Bu değer sayı türünde olmalıdır.',
    ErrorMessage::MUST_BE_STRING => 'Bu değerin türü string olmalıdır.',
    ErrorMessage::MUST_BE_ARRAY => 'Bu değer dizi türünde olmalıdır.',
    ErrorMessage::ARRAY_OVERFLOW => 'Bu değer {{ maximum_elements }} öğe ile sınırlıdır.',
    ErrorMessage::INVALID_CHOICE => 'Bu değer geçerli bir seçenek değil; beklenen değerler: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Bu değer geçerli bir zaman damgası değil; beklenen format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Bu değer geçerli bir saat dilimi değil.',
    ErrorMessage::INVALID_UID => 'Bu değer geçerli bir UID değil.',
];
