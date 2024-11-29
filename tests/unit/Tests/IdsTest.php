<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\Ids;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Ids::class)]
class IdsTest extends TestCase
{
    public function testCreate(): void
    {
        $key = 'this-is-key';
        $id = Ids::create($key);

        static::assertNotEmpty($id);
        static::assertIsString($id);
        static::assertSame($id, Ids::get($key));
    }

    public function testGetUuid(): void
    {
        $key = 'this-is-key';

        $uuid = Ids::getUuid($key);

        static::assertInstanceOf(Uuid::class, $uuid);
        static::assertSame($uuid->toBinary(), Ids::getBytes($key));
    }

    public function testAll(): void
    {
        Ids::set('this-is-key-1', 'this-is-id-1');
        Ids::set('this-is-key-2', 'this-is-id-2');

        static::assertSame([
            'this-is-key-1' => 'this-is-id-1',
            'this-is-key-2' => 'this-is-id-2',
        ], Ids::all());
    }

    public function testSet(): void
    {
        Ids::set('this-is-key', 'this-is-id');

        static::assertSame('this-is-id', Ids::get('this-is-key'));
    }

    public function testHas(): void
    {
        Ids::set('this-is-key-1', 'this-is-id-1');

        static::assertTrue(Ids::has('this-is-key-1'));
        static::assertFalse(Ids::has('this-is-key-2'));
    }

    public function testGetKey(): void
    {
        Ids::set('this-is-key-1', 'this-is-id-1');

        static::assertSame('this-is-key-1', Ids::getKey('this-is-id-1'));
        static::assertNull(Ids::getKey('this-is-id-2'));
    }

    public function testReset(): void
    {
        Ids::set('this-is-key-1', 'this-is-id-1');

        static::assertEquals(
            ['this-is-key-1' => 'this-is-id-1'],
            Ids::all(),
        );

        Ids::reset();

        static::assertEmpty(Ids::all());
    }

    public function testEmpty(): void
    {
        // ensure that Ids are always reset before tests
        static::assertEmpty(Ids::all());
    }
}
