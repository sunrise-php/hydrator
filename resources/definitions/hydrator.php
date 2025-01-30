<?php

declare(strict_types=1);

use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;

use function DI\create;
use function DI\get;

return [
    'hydrator.context' => [],
    'hydrator.type_converters' => [],

    HydratorInterface::class => create(Hydrator::class)
        ->constructor(
            get('hydrator.context'),
            get('hydrator.type_converters'),
        ),
];
