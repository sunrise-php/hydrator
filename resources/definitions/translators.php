<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\TranslationDomain;
use Sunrise\Translator\Translator\DirectoryTranslator;

use function DI\add;
use function DI\create;

return [
    'translator.translators' => add([
        create(DirectoryTranslator::class)
            ->constructor(
                TranslationDomain::HYDRATOR,
                __DIR__ . '/../translations',
            ),
    ]),
];
