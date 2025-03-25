<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'ഈ മൂല്യം നൽകണം.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'ഈ മൂല്യം ശൂന്യമാകാൻ പാടില്ല.',
    ErrorMessage::MUST_BE_BOOLEAN => 'ഈ മൂല്യം ബൂളിയൻ തരം ആയിരിക്കണം.',
    ErrorMessage::MUST_BE_INTEGER => 'ഈമൂല്യം പൂർത്തീകരിക്കേണ്ടത് integer തരം ആയിരിക്കണം.',
    ErrorMessage::MUST_BE_NUMBER => 'ഈ മൂല്യം സംഖ്യാ തരം ആയിരിക്കണം.',
    ErrorMessage::MUST_BE_STRING => 'ഈ മൂല്യം string തരം ആകണം.',
    ErrorMessage::MUST_BE_ARRAY => 'ഈ മൂല്യം array തരം ആയിരിക്കണം.',
    ErrorMessage::ARRAY_OVERFLOW => 'ഈ മൂല്യം {{ maximum_elements }} ഘടകങ്ങളിലേക്ക് പരിമിതപ്പെടുത്തിയിരിക്കുന്നു.',
    ErrorMessage::INVALID_CHOICE => 'ഈ മൂല്യം സാധുവായ തിരഞ്ഞെടുപ്പല്ല; പ്രതീക്ഷിക്കപ്പെട്ട മൂല്യങ്ങൾ: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'ഈ മൂല്യം ഒരു സാധുവായ ടൈംസ്റ്റാംപ് അല്ല; പ്രതീക്ഷിക്കുന്ന ഫോർമാറ്റ്: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'ഈ മൂല്യം സാധുവായ സമയംമേഖല അല്ല.',
    ErrorMessage::INVALID_UID => 'ഈ മൂല്യം സാധുവായ UID അല്ല.',
];
