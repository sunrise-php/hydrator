<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Exception;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;

class HydratorTest extends TestCase
{
    public function testContracts() : void
    {
        $hydrator = new Hydrator();

        $this->assertInstanceOf(HydratorInterface::class, $hydrator);
    }

    public function testHydrate() : void
    {
        $data = [];
        $data['statical'] = '813ea72c-6763-4596-a4d6-b478efed61bb';
        $data['nullable'] = null;
        $data['required'] = '9f5c273e-1dca-4c2d-ac81-7d6b03b169f4';
        $data['boolean'] = true;
        $data['integer'] = 42;
        $data['number'] = 123.45;
        $data['string'] = 'db7614d4-0a81-437b-b2cf-c536ad229c97';
        $data['array'] = ['foo' => 'bar'];
        $data['object'] = (object) ['foo' => 'bar'];
        $data['dateTime'] = '2038-01-19 03:14:08';
        $data['dateTimeImmutable'] = '2038-01-19 03:14:08';
        $data['bar'] = ['value' => '9898fb3b-ffb0-406c-bda6-b516423abde7'];
        $data['barCollection'][] = ['value' => 'd85c17b6-6e2c-4e2d-9eba-e1dd59b75fe3'];
        $data['barCollection'][] = ['value' => '5a8019aa-1c15-4c7c-8beb-1783c3d8996b'];
        $data['non-normalized'] = 'f76c4656-431a-4337-9ba9-5440611b37f1';

        $object = (new Hydrator)->hydrate(Fixtures\Foo::class, $data);

        $this->assertNotSame($data['statical'], $object::$statical);
        $this->assertSame($data['nullable'], $object->nullable);
        $this->assertSame($data['required'], $object->required);
        $this->assertSame($data['boolean'], $object->boolean);
        $this->assertSame($data['integer'], $object->integer);
        $this->assertSame($data['number'], $object->number);
        $this->assertSame($data['string'], $object->string);
        $this->assertSame($data['array'], $object->array);
        $this->assertSame($data['object'], $object->object);
        $this->assertSame($data['dateTime'], $object->dateTime->format('Y-m-d H:i:s'));
        $this->assertSame($data['dateTimeImmutable'], $object->dateTimeImmutable->format('Y-m-d H:i:s'));
        $this->assertSame($data['bar']['value'], $object->bar->value);
        $this->assertSame($data['barCollection'][0]['value'], $object->barCollection->get(0)->value);
        $this->assertSame($data['barCollection'][1]['value'], $object->barCollection->get(1)->value);
        $this->assertSame($data['non-normalized'], $object->normalized);
    }
}
