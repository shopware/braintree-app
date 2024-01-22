<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Util;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Swag\Braintree\Braintree\Util\ReportClientFactory;

#[CoversClass(ReportClientFactory::class)]
class ReportClientFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $clientHistory = [];
        $clientHandler = new MockHandler();
        $clientHandler->append(new Response());
        $clientHandlerStack = HandlerStack::create($clientHandler);
        $clientHandlerStack->push(Middleware::history($clientHistory));

        $client = ReportClientFactory::createClient(['base_uri' => 'https://example.com', 'handler' => $clientHandlerStack]);
        $client->get('/foo/bar');

        static::assertCount(1, $clientHistory);

        static::assertArrayHasKey('request', $clientHistory[0]);
        static::assertInstanceOf(Request::class, $clientHistory[0]['request']);

        $uri = $clientHistory[0]['request']->getUri();

        static::assertInstanceOf(UriInterface::class, $uri);
        static::assertSame('https://example.com/foo/bar', (string) $uri);
    }
}
