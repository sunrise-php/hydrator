<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'ਇਹ ਮੁੱਲ ਪ੍ਰਦਾਨ ਕੀਤਾ ਜਾਣਾ ਚਾਹੀਦਾ ਹੈ।',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'ਇਹ ਮੁੱਲ ਖਾਲੀ ਨਹੀਂ ਹੋਣਾ ਚਾਹੀਦਾ।',
    ErrorMessage::MUST_BE_BOOLEAN => 'ਇਹ ਮੁੱਲ ਬੂਲੀਅਨ ਕਿਸਮ ਦਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ।',
    ErrorMessage::MUST_BE_INTEGER => 'ਇਸ ਮੁੱਲ ਦਾ ਪ੍ਰਕਾਰ ਪੂਰਨ ਅੰਕ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ।',
    ErrorMessage::MUST_BE_NUMBER => 'ਇਹ ਮੁੱਲ ਗਿਣਤੀ ਕਿਸਮ ਦਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ।',
    ErrorMessage::MUST_BE_STRING => 'ਇਹ ਮੂਲ੍ਯ string ਕਿਸਮ ਦਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ।',
    ErrorMessage::MUST_BE_ARRAY => 'ਪ੍ਰਕਾਰ array ਦਾ ਹੋਣਾ ਲਾਜ਼ਮੀ ਹੈ।',
    ErrorMessage::ARRAY_OVERFLOW => 'ਇਸ ਮੁੱਲ ਨੂੰ {{ maximum_elements }} ਤੱਤਾਂ ਤੱਕ ਸੀਮਿਤ ਕੀਤਾ ਗਿਆ ਹੈ।',
    ErrorMessage::INVALID_CHOICE => 'ਇਹ ਮুলਵਤ ਚੋਣ ਨਹੀਂ ਹੈ; ਉਮੀਦ ਕੀਤੀਆਂ ਮੁੱਲ: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'ਇਹ ਮੁੱਲ ਸਹੀ ਟਾਈਮਸਟੈਂਪ ਨਹੀਂ ਹੈ; ਉਮੀਦ ਕੀਤੀ ਫਾਰਮੈਟ: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'ਇਹ ਮਾਤਰਾ ਸਹੀ ਸਮਾਂ ਖੇਤਰ ਨਹੀਂ ਹੈ।',
    ErrorMessage::INVALID_UID => 'ਇਹ ਮੁੱਲ ਇੱਕ ਵੈਧ UID ਨਹੀਂ ਹੈ।',
];
