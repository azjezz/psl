<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\URL;
use Throwable;

use function json_decode;
use function ltrim;
use function rtrim;
use function strtolower;

use const JSON_THROW_ON_ERROR;
use const Psl\HTTP\Message\METHOD_GET;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();

        try {
            $request = new Request(method: METHOD_GET, url: $this->getUrl('/get'));
            $this->client->send($request);
        } catch (Throwable) {
            static::markTestSkipped('httpbun is not reachable at ' . $this->getUrlString('/get'));
        }
    }

    protected function getUrlString(string $path): string
    {
        $base = $_SERVER['HTTPBUN_URL'] ?? 'http://localhost:3090';

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    protected function getUrl(string $path): URL\URL
    {
        return URL\parse($this->getUrlString($path));
    }

    protected function sendGet(string $path): Transaction
    {
        return $this->sendRequest(METHOD_GET, $path);
    }

    protected function sendRequest(string $method, string $path): Transaction
    {
        $request = new Request(method: $method, url: $this->getUrl($path));

        return $this->client->send($request);
    }

    protected function decodeJsonBody(Transaction $tx): array
    {
        $body = $tx->response->body?->readAll() ?? '';

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function normalizeHeaderKeys(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower($key)] = $value;
        }

        return $normalized;
    }
}
