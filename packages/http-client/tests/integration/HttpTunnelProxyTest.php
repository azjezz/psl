<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\Request;
use Psl\IO\MemoryHandle;
use Psl\URL;
use Throwable;

use function json_decode;
use function strlen;

use const Psl\HTTP\Message\METHOD_GET;
use const Psl\HTTP\Message\METHOD_POST;

final class HttpTunnelProxyTest extends TestCase
{
    private string $httpbunUrl;
    private string $tunnelUrl;

    protected function setUp(): void
    {
        $this->tunnelUrl = $_SERVER['HTTP_TUNNEL_URL'] ?? '';
        $this->httpbunUrl = $_SERVER['HTTPBUN_URL'] ?? '';

        if ($this->tunnelUrl === '' || $this->httpbunUrl === '') {
            static::markTestSkipped('HTTP_TUNNEL_URL and HTTPBUN_URL must be set.');
        }

        try {
            $client = $this->createClient();
            $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/get')));
        } catch (Throwable) {
            static::markTestSkipped('Cannot reach httpbun through HTTP tunnel proxy.');
        }
    }

    public function testGetRequestThroughTunnel(): void
    {
        $client = $this->createClient();
        $tx = $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/get')));

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        $json = json_decode($body, true);
        static::assertSame('GET', $json['method']);
    }

    public function testPostRequestThroughTunnel(): void
    {
        $client = $this->createClient();
        $tx = $client->send(new Request(
            method: METHOD_POST,
            url: URL\parse($this->httpbunUrl . '/post'),
            body: new MemoryHandle('hello tunnel'),
        ));

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        $json = json_decode($body, true);
        static::assertSame('POST', $json['method']);
    }

    public function testBodyReadingThroughTunnel(): void
    {
        $client = $this->createClient();
        $tx = $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/bytes/50')));

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(50, strlen($body));
    }

    public function testConcurrentRequestsThroughTunnel(): void
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

    public function testNoTunnelingBypassesProxy(): void
    {
        $client = $this->createClient(['*']);

        $tx = $client->send(new Request(method: METHOD_GET, url: URL\parse($this->httpbunUrl . '/get')));

        static::assertSame(200, $tx->response->status);
    }

    private function createClient(array $skipProxyFor = []): Client
    {
        return new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(
                proxyConfiguration: new ProxyConfiguration(URL\parse($this->tunnelUrl), skipProxyFor: $skipProxyFor),
            ),
        );
    }
}
