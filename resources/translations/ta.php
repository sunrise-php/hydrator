<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'இந்த மதிப்பு வழங்கப்பட வேண்டும்.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'இந்த மறு பெறுமதி காலியாக இருக்கக்கூடாது.',
    ErrorMessage::MUST_BE_BOOLEAN => 'இந்த மதிப்பு boolean வகையாக இருக்க வேண்டும்.',
    ErrorMessage::MUST_BE_INTEGER => 'இந்த மதிப்பு முழுவதும் integer வகையானதாக இருக்க வேண்டும்.',
    ErrorMessage::MUST_BE_NUMBER => 'இந்த மதிப்பு number வகையாக இருக்க வேண்டும்.',
    ErrorMessage::MUST_BE_STRING => 'இந்த மதிப்பு string வகையிலானதாக இருக்க வேண்டியது அவசியம்.',
    ErrorMessage::MUST_BE_ARRAY => 'இந்த மதிப்பு array வகையானது ஆக வேண்டும்.',
    ErrorMessage::ARRAY_OVERFLOW => 'இந்த மதிப்பு {{ maximum_elements }} பொருட்களாகக் குறிக்கப்படுகிறது.',
    ErrorMessage::INVALID_CHOICE => 'இந்த மதிப்பு சரியான தேர்வாக இல்லை; எதிர்பார்க்கப்பட்ட மதிப்புகள்: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'இந்த மதிப்பு சரியான காலமுத்திரையல்ல; எதிர்பார்க்கப்படும் வடிவம்: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'இந்த மதிப்பு செல்லுபடியான நேர மண்டலம் அல்ல.',
    ErrorMessage::INVALID_UID => 'இந்த மதிப்பு செல்லுபடியான UID அல்ல.',
];
