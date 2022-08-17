<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Enum;
use JsonSerializable;
use RuntimeException;

class EnumTest extends TestCase
{
    public function testCases() : void
    {
        $this->assertSame([
            Fixtures\CustomEnum::Int1(),
            Fixtures\CustomEnum::Int2(),
            Fixtures\CustomEnum::String1(),
            Fixtures\CustomEnum::String2(),
        ], Fixtures\CustomEnum::cases());
    }

    public function testCase() : void
    {
        $case = Fixtures\CustomEnum::Int1();
        $this->assertSame('Int1', $case->name());
        $this->assertSame(Fixtures\CustomEnum::Int1, $case->value());

        $case = Fixtures\CustomEnum::Int2();
        $this->assertSame('Int2', $case->name());
        $this->assertSame(Fixtures\CustomEnum::Int2, $case->value());

        $case = Fixtures\CustomEnum::String1();
        $this->assertSame('String1', $case->name());
        $this->assertSame(Fixtures\CustomEnum::String1, $case->value());

        $case = Fixtures\CustomEnum::String2();
        $this->assertSame('String2', $case->name());
        $this->assertSame(Fixtures\CustomEnum::String2, $case->value());
    }

    public function testUnknownCase() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Enum case ' . Fixtures\CustomEnum::class
                                    . '::Another not found');

        Fixtures\CustomEnum::Another();
    }

    public function testTryFrom() : void
    {
        $this->assertSame(Fixtures\CustomEnum::Int1(), Fixtures\CustomEnum::tryFrom(Fixtures\CustomEnum::Int1));
        $this->assertSame(Fixtures\CustomEnum::Int2(), Fixtures\CustomEnum::tryFrom(Fixtures\CustomEnum::Int2));
        $this->assertSame(Fixtures\CustomEnum::String1(), Fixtures\CustomEnum::tryFrom(Fixtures\CustomEnum::String1));
        $this->assertSame(Fixtures\CustomEnum::String2(), Fixtures\CustomEnum::tryFrom(Fixtures\CustomEnum::String2));
    }

    public function testTryFromWithUnknownValue() : void
    {
        $this->assertNull(Fixtures\CustomEnum::tryFrom(Fixtures\CustomEnum::Another));
        $this->assertNull(Fixtures\CustomEnum::tryFrom('unknown'));
    }

    public function testCache() : void
    {
        $this->assertSame(Fixtures\CustomEnum::cases(), Fixtures\CustomEnum::cases());
        $this->assertSame(Fixtures\CustomEnum::Int1(), Fixtures\CustomEnum::Int1());
        $this->assertSame(Fixtures\CustomEnum::Int2(), Fixtures\CustomEnum::Int2());
        $this->assertSame(Fixtures\CustomEnum::String1(), Fixtures\CustomEnum::String1());
        $this->assertSame(Fixtures\CustomEnum::String2(), Fixtures\CustomEnum::String2());
    }

    public function testJsonSerialize() : void
    {
        $case = Fixtures\CustomEnum::Int1();

        $this->assertInstanceOf(JsonSerializable::class, $case);
        $this->assertSame($case->value(), $case->jsonSerialize());
    }
}
