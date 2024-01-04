<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * @extends Collection<array-key, BooleanAssociation>
 */
final class BooleanAssociationCollection extends Collection
{
    public function __construct(BooleanAssociation ...$elements)
    {
    }
}
