<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Filter;

use Sunrise\Hydrator\Filter\EmptyStringToNull;
use PHPUnit\Framework\TestCase;

final class EmptyStringToNullTest extends TestCase
{
    public function testEmptyString(): void
    {
        $this->assertNull((new EmptyStringToNull())(''));
    }

    public function testNotEmptyString(): void
    {
        $this->assertSame('foo', (new EmptyStringToNull())('foo'));
    }
}
