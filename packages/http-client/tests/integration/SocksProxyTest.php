<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Message\Request;
use Psl\Socks;
use Psl\URL;
use Throwable;

use function json_decode;
use function strlen;

use const Psl\HTTP\Message\METHOD_GET;

final class SocksProxyTest extends TestCase
{
    private string $httpbunUrl;
    private Socks\Configuration $proxyConfig;

    protected function setUp(): void
    {
        $host = $_SERVER['SOCKS_PROXY_HOST'] ?? '';
        $port = $_SERVER['SOCKS_PROXY_PORT'] ?? '';
        $this->httpbunUrl = $_SERVER['HTTPBUN_URL'] ?? '';

        if ($host === '' || $port === '' || $this->httpbunUrl === '') {
            static::markTestSkipped('SOCKS_PROXY_HOST, SOCKS_PROXY_PORT, and HTTPBUN_URL must be set.');
        }

        $this->proxyConfig = new Socks\Configuration($host, (int) $port);

        try {
            $client = $this->createClient();
            $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/get')));
        } catch (Throwable) {
            static::markTestSkipped('Cannot reach httpbun through SOCKS proxy.');
        }
    }

    public function testGetRequestThroughProxy(): void
    {
        $client = $this->createClient();
        $tx = $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/get')));

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        $json = json_decode($body, true);
        static::assertSame('GET', $json['method']);
    }

    public function testBodyReadingThroughProxy(): void
    {
        $client = $this->createClient();
        $tx = $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/bytes/50')));

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(50, strlen($body));
    }

    public function testConcurrentRequestsThroughProxy(): void
    {
        $client = $this->createClient();
        $url = URL\parse($this->httpbunUrl . '/get');

        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $tasks[] = static function () use ($client, $url): int {
                $tx = $client->send(new Request(method: METHOD_GET, url: $url));
                $tx->response->body?->readAll();
                return $tx->response->status;
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(5, $results);
        foreach ($results as $status) {
            static::assertSame(200, $status);
        }
    }

    private function createClient(): Client
    {
        return new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(proxy: $this->proxyConfig),
        );
    }
}
