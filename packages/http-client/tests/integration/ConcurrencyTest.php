<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Integration;

use Psl\Async;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO\MemoryHandle;
use Psl\URL;

use function json_decode;
use function microtime;
use function str_repeat;
use function strlen;

use const JSON_THROW_ON_ERROR;
use const Psl\HTTP\Message\METHOD_DELETE;
use const Psl\HTTP\Message\METHOD_GET;
use const Psl\HTTP\Message\METHOD_HEAD;
use const Psl\HTTP\Message\METHOD_POST;
use const Psl\HTTP\Message\METHOD_PUT;

/**
 * @mago-expect lint:kan-defect
 */
final class ConcurrencyTest extends AbstractIntegrationTestCase
{
    public function testTenConcurrentGetRequests(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/get');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(10, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testFiftyConcurrentGetRequests(): void
    {
        $tasks = [];
        for ($i = 0; $i < 50; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/get');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(50, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testConcurrentDifferentEndpoints(): void
    {
        $paths = ['/get', '/ip', '/headers', '/html', '/robots.txt'];
        $tasks = [];
        foreach ($paths as $i => $path) {
            $tasks[$i] = fn(): Transaction => $this->sendGet($path);
        }

        $results = Async\concurrently($tasks);

        static::assertCount(5, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testConcurrentDifferentMethods(): void
    {
        $methods = [METHOD_GET, METHOD_POST, METHOD_PUT, METHOD_DELETE];
        $tasks = [];
        foreach ($methods as $i => $method) {
            $tasks[$i] = fn(): Transaction => $this->sendRequest($method, '/any');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(4, $results);
        foreach ($results as $i => $tx) {
            static::assertSame(200, $tx->response->status);
            $json = $this->decodeJsonBody($tx);
            static::assertSame($methods[$i], $json['method']);
        }
    }

    public function testConcurrentDifferentStatusCodes(): void
    {
        $codes = [200, 201, 204, 418];
        $tasks = [];
        foreach ($codes as $i => $code) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/status/' . $code);
        }

        $results = Async\concurrently($tasks);

        static::assertCount(4, $results);
        foreach ($results as $i => $tx) {
            static::assertSame($codes[$i], $tx->response->status);
        }
    }

    public function testConcurrentPostRequests(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $body = 'request-' . $i;
            $tasks[$i] = function () use ($body): Transaction {
                $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/post')));
                $request = $request->withBody(new MemoryHandle($body));

                return $this->client->send($request);
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(10, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testConcurrentWithBodyReading(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/get');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(10, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
            $json = $this->decodeJsonBody($tx);
            static::assertIsArray($json);
            static::assertArrayHasKey('method', $json);
        }
    }

    public function testSequentialRequestsReuseConnection(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $tx = $this->sendGet('/get');

            static::assertSame(200, $tx->response->status);
            $tx->response->body?->readAll();
        }
    }

    public function testSequentialRequestsDifferentPaths(): void
    {
        $paths = ['/get', '/ip', '/headers'];

        foreach ($paths as $path) {
            $tx = $this->sendGet($path);

            static::assertSame(200, $tx->response->status);
            $tx->response->body?->readAll();
        }
    }

    public function testConcurrentWithHeaders(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $id = 'req-' . $i;
            $tasks[$i] = function () use ($id): Transaction {
                $request = new Request(method: METHOD_GET, url: URL\parse($this->getUrlString('/headers')));
                $request = $request->withHeader('X-Request-Id', $id);

                return $this->client->send($request);
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(10, $results);
        foreach ($results as $i => $tx) {
            static::assertSame(200, $tx->response->status);
            $json = $this->decodeJsonBody($tx);
            $headers = $this->normalizeHeaderKeys($json['headers']);
            static::assertSame('req-' . $i, $headers['x-request-id']);
        }
    }

    public function testStreamingResponseBytes(): void
    {
        $tx = $this->sendGet('/bytes/50');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(50, strlen($body));
    }

    public function testStreamingResponseRange(): void
    {
        $tx = $this->sendGet('/range/100');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(100, strlen($body));
    }

    public function testDripEndpoint(): void
    {
        $tx = $this->sendGet('/drip?duration=1&numbytes=5&code=200&delay=0');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(5, strlen($body));
    }

    public function testDripLinesEndpoint(): void
    {
        $tx = $this->sendGet('/drip-lines?duration=1&numbytes=5&code=200&delay=0');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertNotEmpty($body);
    }

    public function testDelayedResponse(): void
    {
        $tx = $this->sendGet('/delay/1');

        static::assertSame(200, $tx->response->status);
    }

    public function testConcurrentDelayedRequests(): void
    {
        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/delay/1');
        }

        $start = microtime(true);
        $results = Async\concurrently($tasks);
        $elapsed = microtime(true) - $start;

        static::assertCount(5, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }

        static::assertLessThan(4.0, $elapsed, 'Concurrent delayed requests should run in parallel, not sequentially');
    }

    public function testMixStatusAndHeader(): void
    {
        $tx = $this->sendGet('/mix/s=201/h=x-custom:hello');

        static::assertSame(201, $tx->response->status);
        $value = $tx->response->headers->get('X-Custom');
        static::assertNotNull($value);
        static::assertSame('hello', $value);
    }

    public function testMixWithBody(): void
    {
        $tx = $this->sendGet('/mix/s=200/h=x-test:works');

        static::assertSame(200, $tx->response->status);
        $header = $tx->response->headers->get('X-Test');
        static::assertNotNull($header);
        static::assertSame('works', $header);
    }

    public function testIpEndpoint(): void
    {
        $tx = $this->sendGet('/ip');

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertArrayHasKey('origin', $json);
        static::assertNotEmpty($json['origin']);
    }

    public function testDenyEndpoint(): void
    {
        $tx = $this->sendGet('/deny');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertNotEmpty($body);
    }

    public function testLargeResponseBody(): void
    {
        $tx = $this->sendGet('/range/500');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(500, strlen($body));
    }

    public function testPostLargeBodyConcurrent(): void
    {
        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $payload = str_repeat('X', 5000);
            $tasks[$i] = function () use ($payload): Transaction {
                $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/payload')));
                $request = $request->withBody(new MemoryHandle($payload));

                return $this->client->send($request);
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(5, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame(5000, strlen($body));
        }
    }

    public function testConcurrentMixedMethodsAndBodies(): void
    {
        $tasks = [];

        $tasks[0] = fn(): Transaction => $this->sendGet('/get');

        $tasks[1] = function (): Transaction {
            $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/post')));
            $request = $request->withBody(new MemoryHandle('post-body'));

            return $this->client->send($request);
        };

        $tasks[2] = function (): Transaction {
            $request = new Request(method: METHOD_PUT, url: URL\parse($this->getUrlString('/any')));
            $request = $request->withBody(new MemoryHandle('put-body'));

            return $this->client->send($request);
        };

        $tasks[3] = function (): Transaction {
            $request = new Request(method: METHOD_DELETE, url: URL\parse($this->getUrlString('/any')));

            return $this->client->send($request);
        };

        $results = Async\concurrently($tasks);

        static::assertCount(4, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testMultipleSequentialPostsWithDifferentBodies(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $payload = 'body-' . $i;
            $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/payload')));
            $request = $request->withBody(new MemoryHandle($payload));
            $tx = $this->client->send($request);

            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame($payload, $body);
        }
    }

    public function testResponseHeaderContentType(): void
    {
        $tx = $this->sendGet('/get');

        static::assertSame(200, $tx->response->status);
        $contentType = $tx->response->headers->get('Content-Type');
        static::assertNotNull($contentType);
        static::assertStringContainsString('application/json', $contentType);
    }

    public function testResponseHeaderContentTypeHtml(): void
    {
        $tx = $this->sendGet('/html');

        static::assertSame(200, $tx->response->status);
        $contentType = $tx->response->headers->get('Content-Type');
        static::assertNotNull($contentType);
        static::assertStringContainsString('text/html', $contentType);
    }

    public function testEmptyResponseBody(): void
    {
        $tx = $this->sendGet('/status/204');

        static::assertSame(204, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('', $body);
    }

    public function testHeadRequest(): void
    {
        $tx = $this->sendRequest(METHOD_HEAD, '/any');

        static::assertSame(200, $tx->response->status);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('', $body);
    }

    public function testConcurrentThenSequential(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/get');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(10, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
            $tx->response->body?->readAll();
        }

        for ($i = 0; $i < 5; $i++) {
            $tx = $this->sendGet('/get');

            static::assertSame(200, $tx->response->status);
            $tx->response->body?->readAll();
        }
    }

    public function testRequestWithQueryAndHeaders(): void
    {
        $request = new Request(method: METHOD_GET, url: URL\parse($this->getUrlString('/get?foo=bar')));
        $request = $request->withHeader('X-Custom', 'test');
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);

        static::assertArrayHasKey('args', $json);
        static::assertSame('bar', $json['args']['foo']);

        static::assertArrayHasKey('headers', $json);
        $headers = $this->normalizeHeaderKeys($json['headers']);
        static::assertSame('test', $headers['x-custom']);
    }

    public function testPostWithQueryParams(): void
    {
        $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/post?key=value')));
        $request = $request->withHeader('Content-Type', 'text/plain');
        $request = $request->withBody(new MemoryHandle('post-data'));
        $tx = $this->client->send($request);

        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);

        static::assertArrayHasKey('args', $json);
        static::assertSame('value', $json['args']['key']);

        static::assertArrayHasKey('data', $json);
        static::assertSame('post-data', $json['data']);
    }

    public function testConcurrentHundredRequests(): void
    {
        $tasks = [];
        for ($i = 0; $i < 100; $i++) {
            $tasks[$i] = fn(): Transaction => $this->sendGet('/ip');
        }

        $results = Async\concurrently($tasks);

        static::assertCount(100, $results);
        foreach ($results as $tx) {
            static::assertSame(200, $tx->response->status);
        }
    }

    public function testConcurrentPostWithUniquePayloads(): void
    {
        $tasks = [];
        for ($i = 0; $i < 20; $i++) {
            $payload = 'payload-' . $i;
            $tasks[$i] = function () use ($payload): Transaction {
                $request = new Request(method: METHOD_POST, url: URL\parse($this->getUrlString('/payload')));
                $request = $request->withBody(new MemoryHandle($payload));

                return $this->client->send($request);
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(20, $results);
        foreach ($results as $i => $tx) {
            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame('payload-' . $i, $body);
        }
    }

    public function testConnectionPersistenceAfterError(): void
    {
        $tx = $this->sendGet('/status/500');
        static::assertSame(500, $tx->response->status);
        $tx->response->body?->readAll();

        $tx = $this->sendGet('/get');
        static::assertSame(200, $tx->response->status);
        $json = $this->decodeJsonBody($tx);
        static::assertSame('GET', $json['method']);
    }

    public function testResponseBodyPartialRead(): void
    {
        $tx = $this->sendGet('/get');

        static::assertSame(200, $tx->response->status);
        static::assertNotNull($tx->response->body);

        $body = $tx->response->body;
        $accumulated = '';

        while (!$body->reachedEndOfDataSource()) {
            $chunk = $body->read(10);
            if ($chunk === '') {
                break;
            }

            $accumulated .= $chunk;
        }

        static::assertNotEmpty($accumulated);

        $json = json_decode($accumulated, true, 512, JSON_THROW_ON_ERROR);
        static::assertIsArray($json);
        static::assertArrayHasKey('method', $json);
        static::assertSame('GET', $json['method']);
    }
}
