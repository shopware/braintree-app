<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Doctrine\RespectfulUuidGenerator;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(RespectfulUuidGenerator::class)]
class RespectfulUuidGeneratorTest extends TestCase
{
    public function testGenerateId(): void
    {
        $entity = $this->getTestEntity();

        $generator = new RespectfulUuidGenerator();
        $id = $generator->generateId($this->createMock(EntityManager::class), $entity);

        static::assertNotNull($id);
        static::assertTrue(Uuid::isValid($id->toRfc4122()));
    }

    public function testGenerateIdWithNullEntity(): void
    {
        $generator = new RespectfulUuidGenerator();
        $id = $generator->generateId($this->createMock(EntityManager::class), null);

        static::assertNull($id);
    }

    public function testGenerateIdWithNonEntity(): void
    {
        $entity = new \stdClass();

        $generator = new RespectfulUuidGenerator();

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Class stdClass not supported for respectful uuid generation. Use Swag\Braintree\Entity\Contract\EntityInterface instead');

        $generator->generateId($this->createMock(EntityManager::class), $entity);
    }

    public function testGenerateIdWithExistingId(): void
    {
        $uuid = Uuid::v7();

        $entity = $this->getTestEntity($uuid);

        $generator = new RespectfulUuidGenerator();
        $id = $generator->generateId($this->createMock(EntityManager::class), $entity);

        static::assertNotNull($id);
        static::assertSame($uuid, $id);
    }

    private function getTestEntity(?Uuid $id = null): EntityInterface
    {
        return new class($id) implements EntityInterface {
            public function __construct(private ?Uuid $id = null)
            {
            }

            public function getId(): ?Uuid
            {
                return $this->id;
            }

            public function setId(?Uuid $id): EntityInterface
            {
                $this->id = $id;

                return $this;
            }

            public function getCreatedAt(): \DateTimeInterface
            {
                return new \DateTime();
            }

            public function setCreatedAt(\DateTimeInterface $createdAt): EntityInterface
            {
                return $this;
            }

            public function getUpdatedAt(): ?\DateTimeInterface
            {
                return null;
            }

            public function setUpdatedAt(?\DateTimeInterface $updatedAt): EntityInterface
            {
                return $this;
            }
        };
    }
}
