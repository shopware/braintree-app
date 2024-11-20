<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

final class CreditCardTestCredentials
{
    public const CREDIT_CARD_VALID_AMEX = '378282246310005';
    public const CREDIT_CARD_VALID_MASTER_CARD = '5555555555554444';
    public const CREDIT_CARD_VALID_VISA = '4111111111111111';

    public const CREDIT_CARD_INVALID_AMEX = '378734493671000';
    public const CREDIT_CARD_INVALID_MASTER_CARD = '5105105105105100';
    public const CREDIT_CARD_INVALID_VISA = '4000111111111115';

    public const CREDIT_CARD_NONCE_AMEX_VALID = 'fake-valid-amex-nonce';
    public const CREDIT_CARD_NONCE_MASTER_CARD_VALID = 'fake-valid-mastercard-nonce';
    public const CREDIT_CARD_NONCE_UNKNOWN_VALID = 'fake-valid-nonce';
    public const CREDIT_CARD_NONCE_VISA_VALID = 'fake-valid-visa-nonce';

    public const CREDIT_CARD_NONCE_AMEX_INVALID = 'fake-processor-declined-amex-nonce';
    public const CREDIT_CARD_NONCE_VISA_INVALID = 'fake-processor-declined-visa-nonce';
    public const CREDIT_CARD_NONCE_MASTER_CARD_INVALID = 'fake-processor-declined-mastercard-nonce';

    public const CREDIT_CARD_NONCE_CVV_VALID = 'fake-three-digit-cvv-only-nonce';
    public const CREDIT_CARD_NONCE_CVV_INVALID = 'fake-three-digit-cvv-only-n-response-nonce';

    public static function validCreditCardNumber(string $creditCard = 'visa'): string
    {
        return match ($creditCard) {
            'amex' => self::CREDIT_CARD_VALID_AMEX,
            'mastercard' => self::CREDIT_CARD_VALID_MASTER_CARD,
            default => self::CREDIT_CARD_VALID_VISA,
        };
    }

    public static function invalidCreditCardNumber(string $creditCard = 'visa'): string
    {
        return match ($creditCard) {
            'mastercard' => self::CREDIT_CARD_INVALID_MASTER_CARD,
            'amex' => self::CREDIT_CARD_INVALID_AMEX,
            default => self::CREDIT_CARD_INVALID_VISA,
        };
    }

    public static function validNonce(string $creditCard = 'visa'): string
    {
        return match ($creditCard) {
            'amex' => self::CREDIT_CARD_NONCE_AMEX_VALID,
            'mastercard' => self::CREDIT_CARD_NONCE_MASTER_CARD_VALID,
            'visa' => self::CREDIT_CARD_NONCE_VISA_VALID,
            default => self::CREDIT_CARD_NONCE_UNKNOWN_VALID,
        };
    }

    public static function invalidNonce(string $creditCard = 'visa'): string
    {
        return match ($creditCard) {
            'amex' => self::CREDIT_CARD_NONCE_AMEX_INVALID,
            'mastercard' => self::CREDIT_CARD_NONCE_MASTER_CARD_INVALID,
            default => self::CREDIT_CARD_NONCE_VISA_INVALID,
        };
    }

    public static function validCVV(): string
    {
        return self::CREDIT_CARD_NONCE_CVV_VALID;
    }

    public static function invalidCVV(): string
    {
        return self::CREDIT_CARD_NONCE_CVV_INVALID;
    }

    public static function validTransactionAmount(): float
    {
        return \mt_rand(1, 199999) / 100;
    }

    public static function processorDeclinedTransactionAmount(): float
    {
        return \mt_rand(200000, 299999) / 100;
    }

    public static function settlementDeclined(): float
    {
        return \mt_rand(400300, 500000) / 100;
    }
}