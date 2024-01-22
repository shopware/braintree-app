<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Util;

use GuzzleHttp\Client;

class ReportClientFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public static function createClient(array $config = []): Client
    {
        return new Client($config);
    }
}
