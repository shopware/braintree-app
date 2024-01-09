<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\IdsCollection;
use Symfony\Component\Uid\Uuid;

#[CoversClass(IdsCollection::class)]
class IdsCollectionTest extends TestCase
{
    public function testCreate(): void
    {
        $ids = new IdsCollection();
        $key = 'this-is-key';
        $id = $ids->create($key);

        static::assertNotEmpty($id);
        static::assertIsString($id);
        static::assertSame($id, $ids->get($key));
    }

    public function testGetUuid(): void
    {
        $key = 'this-is-key';

        $ids = new IdsCollection();
        $uuid = $ids->getUuid($key);

        static::assertInstanceOf(Uuid::class, $uuid);
        static::assertSame($uuid->toBinary(), $ids->getBytes($key));
    }

    public function testAll(): void
    {
        $ids = new IdsCollection([
            'this-is-key-1' => 'this-is-id-1',
            'this-is-key-2' => 'this-is-id-2',
        ]);

        static::assertSame([
            'this-is-key-1' => 'this-is-id-1',
            'this-is-key-2' => 'this-is-id-2',
        ], $ids->all());
    }

    public function testSet(): void
    {
        $key = 'this-is-key';
        $value = 'this-is-id';

        $ids = new IdsCollection();
        $ids->set($key, $value);

        static::assertSame($value, $ids->get($key));
    }

    public function testHas(): void
    {
        $ids = new IdsCollection([
            'this-is-key-1' => 'this-is-id-1',
        ]);

        static::assertTrue($ids->has('this-is-key-1'));
        static::assertFalse($ids->has('this-is-key-2'));
    }

    public function testGetKey(): void
    {
        $ids = new IdsCollection([
            'this-is-key-1' => 'this-is-id-1',
        ]);

        static::assertSame('this-is-key-1', $ids->getKey('this-is-id-1'));
        static::assertNull($ids->getKey('this-is-id-2'));
    }
}
