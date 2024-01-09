<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Dto;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;

#[IgnoreClassForCodeCoverage(BraintreeConfig::class)]
class BraintreeConfig
{
    public ?string $merchantId = null;

    public ?string $publicKey = null;

    public ?string $privateKey = null;
}
