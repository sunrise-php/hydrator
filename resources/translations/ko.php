<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => '이 값은 제공되어야 합니다.',
    ErrorMessage::MUST_NOT_BE_EMPTY => '이 값은 비워 둘 수 없습니다.',
    ErrorMessage::MUST_BE_BOOLEAN => '이 값은 boolean 유형이어야 합니다.',
    ErrorMessage::MUST_BE_INTEGER => '이 값은 정수형이어야 합니다.',
    ErrorMessage::MUST_BE_NUMBER => '이 값은 숫자 유형이어야 합니다.',
    ErrorMessage::MUST_BE_STRING => '이 값은 문자열 타입이어야 합니다.',
    ErrorMessage::MUST_BE_ARRAY => '이 값은 배열 타입이어야 합니다.',
    ErrorMessage::ARRAY_OVERFLOW => '이 값은 {{ maximum_elements }} 요소로 제한됩니다.',
    ErrorMessage::INVALID_CHOICE => '이 값은 유효한 선택이 아닙니다. 예상 값: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => '이 값은 유효한 타임스탬프가 아닙니다. 예상 형식: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => '이 값은 유효한 시간대가 아닙니다.',
    ErrorMessage::INVALID_UID => '이 값은 유효한 UID가 아닙니다.',
];
