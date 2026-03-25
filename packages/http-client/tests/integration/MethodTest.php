<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use Psl\HTTP\Message\Request;
use Psl\IO\MemoryHandle;

use function Psl\URL\parse;
use function str_repeat;
use function strlen;

use const Psl\HTTP\Message\METHOD_DELETE;
use const Psl\HTTP\Message\METHOD_GET;
use const Psl\HTTP\Message\METHOD_PATCH;
use const Psl\HTTP\Message\METHOD_POST;
use const Psl\HTTP\Message\METHOD_PUT;

final class MethodTest extends AbstractIntegrationTestCase
{
    public function testGetRequest(): void
    {
        $tx = $this->sendGet('/get');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('GET', $json['method']);
    }

    public function testPostRequest(): void
    {
        $tx = $this->sendRequest(METHOD_POST, '/post');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('POST', $json['method']);
    }

    public function testPutRequest(): void
    {
        $tx = $this->sendRequest(METHOD_PUT, '/put');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('PUT', $json['method']);
    }

    public function testPatchRequest(): void
    {
        $tx = $this->sendRequest(METHOD_PATCH, '/patch');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('PATCH', $json['method']);
    }

    public function testDeleteRequest(): void
    {
        $tx = $this->sendRequest(METHOD_DELETE, '/delete');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('DELETE', $json['method']);
    }

    public function testAnyMethodGet(): void
    {
        $tx = $this->sendGet('/any');

        static::assertSame(200, $tx->response->status);
    }

    public function testAnyMethodPost(): void
    {
        $tx = $this->sendRequest(METHOD_POST, '/any');

        static::assertSame(200, $tx->response->status);
    }

    public function testAnyMethodWithPath(): void
    {
        $tx = $this->sendGet('/any/extra/path');

        static::assertSame(200, $tx->response->status);
    }

    public function testRequestHeadersSent(): void
    {
        $request = new Request(method: METHOD_GET, url: parse($this->getUrlString('/headers')));
        $request = $request->withHeader('X-Test-Header', 'test-value');
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('headers', $json);
        $headers = $this->normalizeHeaderKeys($json['headers']);
        static::assertSame('test-value', $headers['x-test-header']);
    }

    public function testMultipleCustomHeaders(): void
    {
        $request = new Request(method: METHOD_GET, url: parse($this->getUrlString('/headers')));
        $request = $request->withHeader('X-One', 'value-one');
        $request = $request->withHeader('X-Two', 'value-two');
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        $headers = $this->normalizeHeaderKeys($json['headers']);
        static::assertSame('value-one', $headers['x-one']);
        static::assertSame('value-two', $headers['x-two']);
    }

    public function testHostHeader(): void
    {
        $request = new Request(method: METHOD_GET, url: parse($this->getUrlString('/headers')));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        $headers = $this->normalizeHeaderKeys($json['headers']);
        static::assertArrayHasKey('host', $headers);
        static::assertNotEmpty($headers['host']);
    }

    public function testQueryParameters(): void
    {
        $tx = $this->sendGet('/get?foo=bar&baz=qux');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('args', $json);
        static::assertSame('bar', $json['args']['foo']);
        static::assertSame('qux', $json['args']['baz']);
    }

    public function testQueryParametersEncoded(): void
    {
        $tx = $this->sendGet('/get?key=hello%20world');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('args', $json);
        static::assertSame('hello world', $json['args']['key']);
    }

    public function testPostFormBody(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/post')));
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(new MemoryHandle('name=value&other=test'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('form', $json);
        static::assertSame('value', $json['form']['name']);
        static::assertSame('test', $json['form']['other']);
    }

    public function testPostJsonBody(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/post')));
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody(new MemoryHandle('{"key":"value"}'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('json', $json);
        static::assertSame('value', $json['json']['key']);
    }

    public function testPostRawBody(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/post')));
        $request = $request->withHeader('Content-Type', 'text/plain');
        $request = $request->withBody(new MemoryHandle('hello raw body'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('data', $json);
        static::assertSame('hello raw body', $json['data']);
    }

    public function testPayloadEcho(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/payload')));
        $request = $request->withBody(new MemoryHandle('hello world'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('hello world', $body);
    }

    public function testPayloadEchoPreservesContentType(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/payload')));
        $request = $request->withHeader('Content-Type', 'text/plain');
        $request = $request->withBody(new MemoryHandle('typed payload'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $contentType = $tx->response->headers->get('Content-Type');
        static::assertNotNull($contentType);
        static::assertStringContainsString('text/plain', $contentType);
    }

    public function testEmptyPostBody(): void
    {
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/post')));
        $request = $request->withBody(new MemoryHandle(''));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('POST', $json['method']);
    }

    public function testLargeRequestBody(): void
    {
        $payload = str_repeat('A', 9000);
        $request = new Request(method: METHOD_POST, url: parse($this->getUrlString('/payload')));
        $request = $request->withBody(new MemoryHandle($payload));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame($payload, $body);
    }

    public function testLargeResponseBody(): void
    {
        $tx = $this->sendGet('/bytes/100000');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(100_000, strlen($body));
    }

    public function testStatus200(): void
    {
        $tx = $this->sendGet('/status/200');

        static::assertSame(200, $tx->response->status);
    }

    public function testStatus201(): void
    {
        $tx = $this->sendGet('/status/201');

        static::assertSame(201, $tx->response->status);
    }

    public function testStatus204NoContent(): void
    {
        $tx = $this->sendGet('/status/204');

        static::assertSame(204, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('', $body);
    }

    public function testStatus400(): void
    {
        $tx = $this->sendGet('/status/400');

        static::assertSame(400, $tx->response->status);
    }

    public function testStatus404(): void
    {
        $tx = $this->sendGet('/status/404');

        static::assertSame(404, $tx->response->status);
    }

    public function testStatus500(): void
    {
        $tx = $this->sendGet('/status/500');

        static::assertSame(500, $tx->response->status);
    }

    public function testStatus418Teapot(): void
    {
        $tx = $this->sendGet('/status/418');

        static::assertSame(418, $tx->response->status);
    }

    public function testResponseHeaders(): void
    {
        $tx = $this->sendGet('/response-headers?X-Custom=hello');

        static::assertSame(200, $tx->response->status);
        $value = $tx->response->headers->get('X-Custom');
        static::assertNotNull($value);
        static::assertSame('hello', $value);
    }

    public function testResponseHeadersMultiple(): void
    {
        $tx = $this->sendGet('/response-headers?X-One=1&X-Two=2');

        static::assertSame(200, $tx->response->status);
        $one = $tx->response->headers->get('X-One');
        $two = $tx->response->headers->get('X-Two');
        static::assertNotNull($one);
        static::assertNotNull($two);
        static::assertSame('1', $one);
        static::assertSame('2', $two);
    }

    public function testHtmlResponse(): void
    {
        $tx = $this->sendGet('/html');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertStringContainsString('<html', $body);
    }

    public function testRobotsTxt(): void
    {
        $tx = $this->sendGet('/robots.txt');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertNotEmpty($body);
    }

    public function testBase64Decode(): void
    {
        $tx = $this->sendGet('/base64/SGVsbG8gV29ybGQ=');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('Hello World', $body);
    }

    public function testBytesEndpoint(): void
    {
        $tx = $this->sendGet('/bytes/10000');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(10_000, strlen($body));
    }

    public function testRangeEndpoint(): void
    {
        $tx = $this->sendGet('/range/50');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(50, strlen($body));
    }

    public function testDelayEndpoint(): void
    {
        $tx = $this->sendGet('/delay/1');

        static::assertSame(200, $tx->response->status);
    }
}
