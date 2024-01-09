<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Gateway\Connection;

use Braintree\MerchantAccount;

class BraintreeConnectionStatus
{
    public const STATUS_CONNECTED = MerchantAccount::STATUS_ACTIVE;

    public const STATUS_PENDING = MerchantAccount::STATUS_PENDING;

    public const STATUS_SUSPENDED = MerchantAccount::STATUS_SUSPENDED;

    public const STATUS_DISCONNECTED = 'disconnected';

    /**
     * @enum BraintreeConnectionStatus::STATUS_* $connectionStatus
     */
    public function __construct(
        public string $connectionStatus,
        public ?MerchantAccount $merchantAccount = null,
    ) {
    }

    public static function connected(MerchantAccount $merchantAccount = null): self
    {
        return new self(self::STATUS_CONNECTED, $merchantAccount);
    }

    public static function pending(): self
    {
        return new self(self::STATUS_PENDING);
    }

    public static function suspended(): self
    {
        return new self(self::STATUS_SUSPENDED);
    }

    public static function disconnected(): self
    {
        return new self(self::STATUS_DISCONNECTED);
    }
}
