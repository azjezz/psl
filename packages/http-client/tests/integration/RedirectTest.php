<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\Exception\TooManyRedirectsException;
use Psl\HTTP\Client\RedirectClient;
use Psl\HTTP\Message\Request;
use Psl\IO;

use function json_decode;
use function urlencode;

use const Psl\HTTP\Message\METHOD_GET;
use const Psl\HTTP\Message\METHOD_POST;

final class RedirectTest extends AbstractIntegrationTestCase
{
    public function testSingleRedirect(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect/1'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testTripleRedirect(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect/3'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testFiveRedirects(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect/5'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testAbsoluteRedirectSingle(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/absolute-redirect/1'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testAbsoluteRedirectTriple(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/absolute-redirect/3'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testRedirectToUrl(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        static::assertStringContainsString('"method"', $body);
        static::assertStringContainsString('GET', $body);
    }

    public function testRedirectTo301(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target . '&status=301'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testRedirectTo302(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target . '&status=302'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testRedirectTo307(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target . '&status=307'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testRedirectTo308(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target . '&status=308'));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }

    public function testNoRedirectWithBaseClient(): void
    {
        $client = new Client();
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target . '&status=302'));

        $tx = $client->send($request);

        static::assertSame(302, $tx->response->status);
    }

    public function testNoRedirectReturnsLocationHeader(): void
    {
        $client = new Client();
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target));

        $tx = $client->send($request);

        $location = $tx->response->headers->get('Location');
        static::assertNotNull($location);
    }

    public function testRedirect303ChangesMethodToGet(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(
            method: METHOD_POST,
            url: $this->getUrl('/redirect-to?url=' . $target . '&status=303'),
            body: new IO\MemoryHandle('test body'),
        );

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        $decoded = json_decode($body, true);
        static::assertIsArray($decoded);
        static::assertSame('GET', $decoded['method'] ?? null);
    }

    public function testRedirect307PreservesMethod(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/any'));
        $request = new Request(
            method: METHOD_POST,
            url: $this->getUrl('/redirect-to?url=' . $target . '&status=307'),
            body: new IO\MemoryHandle('test body'),
        );

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        $decoded = json_decode($body, true);
        static::assertIsArray($decoded);
        static::assertSame('POST', $decoded['method'] ?? null);
    }

    public function testRedirect308PreservesMethod(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/any'));
        $request = new Request(
            method: METHOD_POST,
            url: $this->getUrl('/redirect-to?url=' . $target . '&status=308'),
            body: new IO\MemoryHandle('test body'),
        );

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        $decoded = json_decode($body, true);
        static::assertIsArray($decoded);
        static::assertSame('POST', $decoded['method'] ?? null);
    }

    public function testRedirect301ChangesMethodToGet(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(
            method: METHOD_POST,
            url: $this->getUrl('/redirect-to?url=' . $target . '&status=301'),
            body: new IO\MemoryHandle('test body'),
        );

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        $decoded = json_decode($body, true);
        static::assertIsArray($decoded);
        static::assertSame('GET', $decoded['method'] ?? null);
    }

    public function testMaxRedirectsExceeded(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 2);
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect/5'));

        $this->expectException(TooManyRedirectsException::class);
        $client->send($request);
    }

    public function testRedirectChainPreservesCustomHeader(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/headers'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/redirect-to?url=' . $target));
        $request = $request->withHeader('X-Custom', 'test');

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll();
        static::assertNotNull($body);
        static::assertStringContainsString('X-Custom', $body);
    }

    public function testMixEndpointRedirect(): void
    {
        $client = new RedirectClient(new Client(), maxRedirects: 10);
        $target = urlencode($this->getUrlString('/get'));
        $request = new Request(method: METHOD_GET, url: $this->getUrl('/mix/s=302/h=location:' . $target));

        $tx = $client->send($request);

        static::assertSame(200, $tx->response->status);
    }
}
