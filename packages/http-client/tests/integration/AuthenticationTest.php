<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use Psl\HTTP\Client\Client;
use Psl\HTTP\Message\Request;

use function base64_encode;
use function json_decode;

use const Psl\HTTP\Message\METHOD_GET;

final class AuthenticationTest extends AbstractIntegrationTestCase
{
    public function testBasicAuthSuccess(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/basic-auth/user/pass'));
        $request = $request->withHeader('Authorization', 'Basic ' . base64_encode('user:pass'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testBasicAuthWrongPassword(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/basic-auth/user/pass'));
        $request = $request->withHeader('Authorization', 'Basic ' . base64_encode('user:wrong'));

        $tx = $client->send($request);

        static::assertSame(401, $tx->response->status);
    }

    public function testBasicAuthNoCredentials(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/basic-auth/user/pass'));

        $tx = $client->send($request);

        static::assertSame(401, $tx->response->status);
    }

    public function testBearerExpectedTokenCorrect(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/bearer/my-secret-token'));
        $request = $request->withHeader('Authorization', 'Bearer my-secret-token');

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testBearerExpectedTokenWrong(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/bearer/my-secret-token'));
        $request = $request->withHeader('Authorization', 'Bearer wrong-token');

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        $json = json_decode($body, true);
        static::assertFalse($json['authenticated']);
    }

    public function testBearerNoToken(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/bearer/expected'));

        $tx = $client->send($request);

        static::assertSame(401, $tx->response->status);
    }

    public function testCacheNoConditionalHeaders(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cache'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testCacheWithIfNoneMatch(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cache'));
        $request = $request->withHeader('If-None-Match', '"some-etag"');

        $tx = $client->send($request);

        static::assertSame(304, $tx->response->status);
    }

    public function testCacheWithIfModifiedSince(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cache'));
        $request = $request->withHeader('If-Modified-Since', 'Wed, 21 Oct 2015 07:28:00 GMT');

        $tx = $client->send($request);

        static::assertSame(304, $tx->response->status);
    }

    public function testCacheAge(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cache/60'));

        $tx = $client->send($request);

        $cacheControl = $tx->response->headers->get('Cache-Control');
        static::assertNotNull($cacheControl);
        static::assertStringContainsString('max-age=60', $cacheControl);
    }

    public function testEtagMatchReturns304(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/etag/test-etag'));
        $request = $request->withHeader('If-None-Match', 'test-etag');

        $tx = $client->send($request);

        static::assertSame(304, $tx->response->status);
    }

    public function testEtagNoMatchReturns200(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/etag/test-etag'));
        $request = $request->withHeader('If-None-Match', 'other-etag');

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testCookiesEndpoint(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cookies'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        $decoded = json_decode($body, true);
        static::assertIsArray($decoded);
        static::assertArrayHasKey('cookies', $decoded);
    }

    public function testCookiesSetReturnsSetCookieHeader(): void
    {
        $client = new Client();
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/cookies/set/testname/testvalue'));

        $tx = $client->send($request);

        static::assertSame(302, $tx->response->status);
        $setCookie = $tx->response->headers->get('Set-Cookie');
        static::assertNotNull($setCookie);
        static::assertStringContainsString('testname=testvalue', $setCookie);
    }
}
