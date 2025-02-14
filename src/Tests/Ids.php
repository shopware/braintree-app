<?php declare(strict_types=1);

namespace Swag\Braintree\Tests;

use Symfony\Component\Uid\Uuid;

class Ids
{
    /**
     * @var array<string, string>
     */
    protected static array $ids = [];

    public static function create(string $key): string
    {
        if (isset(self::$ids[$key])) {
            return self::$ids[$key];
        }

        return self::$ids[$key] = (string) Uuid::v7();
    }

    public static function get(string $key): string
    {
        return self::create($key);
    }

    public static function getUuid(string $key): Uuid
    {
        return Uuid::fromString(self::create($key));
    }

    public static function getBytes(string $key): string
    {
        return Uuid::fromString(self::create($key))->toBinary();
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$ids;
    }

    public static function set(string $key, string $value): void
    {
        self::$ids[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset(self::$ids[$key]);
    }

    public static function getKey(string $id): ?string
    {
        foreach (self::$ids as $key => $value) {
            if ($value === $id) {
                return $key;
            }
        }

        return null;
    }

    public static function reset(): void
    {
        self::$ids = [];
    }
}
