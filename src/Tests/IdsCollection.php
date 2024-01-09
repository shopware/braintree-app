<?php declare(strict_types=1);

namespace Swag\Braintree\Tests;

use Symfony\Component\Uid\Uuid;

class IdsCollection
{
    /**
     * @var array<string, string>
     */
    protected $ids = [];

    /**
     * @param array<string, string> $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function create(string $key): string
    {
        if (isset($this->ids[$key])) {
            return $this->ids[$key];
        }

        return $this->ids[$key] = (string) Uuid::v7();
    }

    public function get(string $key): string
    {
        return $this->create($key);
    }

    public function getUuid(string $key): Uuid
    {
        return Uuid::fromString($this->create($key));
    }

    public function getBytes(string $key): string
    {
        return Uuid::fromString($this->create($key))->toBinary();
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->ids;
    }

    public function set(string $key, string $value): void
    {
        $this->ids[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->ids[$key]);
    }

    public function getKey(string $id): ?string
    {
        foreach ($this->ids as $key => $value) {
            if ($value === $id) {
                return $key;
            }
        }

        return null;
    }
}
