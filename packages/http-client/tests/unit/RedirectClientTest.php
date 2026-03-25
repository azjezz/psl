<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use Override;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientInterface;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\TooManyRedirectsException;
use Psl\HTTP\Client\RedirectClient;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\URL;

use function array_values;

final class RedirectClientTest extends TestCase
{
    private static function fakeClient(Response ...$responses): ClientInterface
    {
        return new class(array_values($responses)) implements ClientInterface {
            /** @var non-negative-int */
            private int $index = 0;

            /**
             * @param list<Response> $responses
             */
            public function __construct(
                private readonly array $responses,
            ) {}

            /** @var list<Request> */
            public array $requests = [];

            #[Override]
            public function send(
                Request $request,
                SendConfiguration $configuration = new SendConfiguration(),
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $this->requests[] = $request;

                return new Transaction([], null, $this->responses[$this->index++]);
            }
        };
    }

    /** @param int<100, 999> $status */
    private static function redirect(int $status, string $location): Response
    {
        return new Response(status: $status, headers: FieldMap::from([['location', $location]]));
    }

    private static function ok(): Response
    {
        return new Response(status: 200, headers: FieldMap::default(), body: new IO\MemoryHandle('ok'));
    }

    /** @param non-empty-uppercase-string $method */
    private static function request(
        string $method = 'GET',
        string $url = 'http://example.com/',
        null|FieldMap $headers = null,
    ): Request {
        return new Request(method: $method, url: URL\parse($url), headers: $headers ?? FieldMap::default());
    }

    public function testNoRedirect(): void
    {
        $inner = self::fakeClient(self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testFollows301(): void
    {
        $inner = self::fakeClient(self::redirect(301, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testFollows302(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testFollows303(): void
    {
        $inner = self::fakeClient(self::redirect(303, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testFollows307(): void
    {
        $inner = self::fakeClient(self::redirect(307, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testFollows308(): void
    {
        $inner = self::fakeClient(self::redirect(308, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
    }

    public function testDoesNotFollow300(): void
    {
        $inner = self::fakeClient(self::redirect(300, 'http://example.com/new'));
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(300, $tx->response->status);
    }

    public function testDoesNotFollow304(): void
    {
        $inner = self::fakeClient(self::redirect(304, 'http://example.com/new'));
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(304, $tx->response->status);
    }

    public function testDoesNotFollow305(): void
    {
        $inner = self::fakeClient(self::redirect(305, 'http://example.com/proxy'));
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(305, $tx->response->status);
    }

    public function testDoesNotFollow4xx(): void
    {
        $response = new Response(status: 404, headers: FieldMap::from([['location', 'http://example.com/']]));
        $inner = self::fakeClient($response);
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(404, $tx->response->status);
    }

    public function test301RewritesPostToGet(): void
    {
        $inner = self::fakeClient(self::redirect(301, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('POST'));

        static::assertSame('GET', $inner->requests[1]->method);
        static::assertNull($inner->requests[1]->body);
    }

    public function test302RewritesPostToGet(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('POST'));

        static::assertSame('GET', $inner->requests[1]->method);
    }

    public function test303RewritesToGet(): void
    {
        $inner = self::fakeClient(self::redirect(303, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('PUT'));

        static::assertSame('GET', $inner->requests[1]->method);
    }

    public function testGetNotRewrittenOn301(): void
    {
        $inner = self::fakeClient(self::redirect(301, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('GET'));

        static::assertSame('GET', $inner->requests[1]->method);
    }

    public function testHeadNotRewrittenOn303(): void
    {
        $inner = self::fakeClient(self::redirect(303, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('HEAD'));

        static::assertSame('HEAD', $inner->requests[1]->method);
    }

    public function test307PreservesMethod(): void
    {
        $inner = self::fakeClient(self::redirect(307, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('POST'));

        static::assertSame('POST', $inner->requests[1]->method);
    }

    public function test308PreservesMethod(): void
    {
        $inner = self::fakeClient(self::redirect(308, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $client->send(self::request('PUT'));

        static::assertSame('PUT', $inner->requests[1]->method);
    }

    public function testBodyHeadersStrippedOnMethodRewrite(): void
    {
        $inner = self::fakeClient(self::redirect(301, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner);
        $request = self::request('POST', 'http://example.com/', FieldMap::from([
            ['content-type',      'application/json'],
            ['content-length',    '42'],
            ['transfer-encoding', 'chunked'],
            ['accept',            'text/html'],
        ]));
        $client->send($request);

        $redirected = $inner->requests[1];
        static::assertNull($redirected->headers->get('content-type'));
        static::assertNull($redirected->headers->get('content-length'));
        static::assertNull($redirected->headers->get('transfer-encoding'));
        static::assertSame('text/html', $redirected->headers->get('accept'));
    }

    public function testSensitiveHeadersStrippedOnCrossOriginRedirect(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://other.com/'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $request = self::request('GET', 'http://example.com/', FieldMap::from([
            ['authorization',       'Bearer secret'],
            ['cookie',              'session=abc'],
            ['proxy-authorization', 'Basic xyz'],
            ['accept',              'text/html'],
        ]));
        $client->send($request);

        $redirected = $inner->requests[1];
        static::assertNull($redirected->headers->get('authorization'));
        static::assertNull($redirected->headers->get('cookie'));
        static::assertNull($redirected->headers->get('proxy-authorization'));
        static::assertSame('text/html', $redirected->headers->get('accept'));
    }

    public function testSensitiveHeadersKeptOnSameOriginRedirect(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/other'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $request = self::request('GET', 'http://example.com/', FieldMap::from([
            ['authorization', 'Bearer secret'],
            ['cookie',        'session=abc'],
        ]));
        $client->send($request);

        $redirected = $inner->requests[1];
        static::assertSame('Bearer secret', $redirected->headers->get('authorization'));
        static::assertSame('session=abc', $redirected->headers->get('cookie'));
    }

    public function testSensitiveHeadersStrippedOnSchemeChange(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $request = self::request('GET', 'https://example.com/', FieldMap::from([
            ['authorization', 'Bearer secret'],
        ]));
        $client->send($request);

        static::assertNull($inner->requests[1]->headers->get('authorization'));
    }

    public function testSensitiveHeadersStrippedOnPortChange(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com:9090/'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $request = self::request('GET', 'http://example.com/', FieldMap::from([
            ['authorization', 'Bearer secret'],
        ]));
        $client->send($request);

        static::assertNull($inner->requests[1]->headers->get('authorization'));
    }

    public function testSameOriginWithDefaultPort(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com:80/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $request = self::request('GET', 'http://example.com/', FieldMap::from([
            ['authorization', 'Bearer secret'],
        ]));
        $client->send($request);

        static::assertSame('Bearer secret', $inner->requests[1]->headers->get('authorization'));
    }

    public function testRefererSetOnRedirect(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: true);
        $client->send(self::request('GET', 'http://example.com/old?q=1'));

        static::assertSame('http://example.com/old?q=1', $inner->requests[1]->headers->get('referer'));
    }

    public function testRefererStrippedWhenHttpsToHttp(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: true);
        $client->send(self::request('GET', 'https://example.com/secure'));

        static::assertNull($inner->requests[1]->headers->get('referer'));
    }

    public function testRefererSetWhenHttpToHttps(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'https://example.com/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: true);
        $client->send(self::request('GET', 'http://example.com/old'));

        static::assertSame('http://example.com/old', $inner->requests[1]->headers->get('referer'));
    }

    public function testRefererSetWhenHttpsToHttps(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'https://example.com/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: true);
        $client->send(self::request('GET', 'https://example.com/old'));

        static::assertSame('https://example.com/old', $inner->requests[1]->headers->get('referer'));
    }

    public function testRefererNotSetWhenDisabled(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'http://example.com/new'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/old'));

        static::assertNull($inner->requests[1]->headers->get('referer'));
    }

    public function testAbsolutePathRedirect(): void
    {
        $inner = self::fakeClient(self::redirect(302, '/new-path'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/old'));

        $url = $inner->requests[1]->url;
        static::assertNotNull($url);
        static::assertSame('http', $url->scheme);
        static::assertSame('example.com', $url->authority->host->toString());
        static::assertSame('/new-path', $url->path);
    }

    public function testAbsolutePathWithQuery(): void
    {
        $inner = self::fakeClient(self::redirect(302, '/search?q=test'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/old'));

        $url = $inner->requests[1]->url;
        static::assertNotNull($url);
        static::assertSame('/search', $url->path);
        static::assertSame('q=test', $url->query);
    }

    public function testRelativePathRedirect(): void
    {
        $inner = self::fakeClient(self::redirect(302, 'other-page'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/dir/old'));

        $url = $inner->requests[1]->url;
        static::assertNotNull($url);
        static::assertSame('/dir/other-page', $url->path);
    }

    public function testDotSegmentRemoval(): void
    {
        $inner = self::fakeClient(self::redirect(302, '/a/b/../c/./d'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/'));

        $url = $inner->requests[1]->url;
        static::assertNotNull($url);
        static::assertSame('/a/c/d', $url->path);
    }

    public function testRelativePathWithDotSegments(): void
    {
        $inner = self::fakeClient(self::redirect(302, '../other'), self::ok());
        $client = new RedirectClient($inner, autoReferrer: false);
        $client->send(self::request('GET', 'http://example.com/a/b/page'));

        $url = $inner->requests[1]->url;
        static::assertNotNull($url);
        static::assertSame('/a/other', $url->path);
    }

    public function testNoLocationHeaderStopsRedirect(): void
    {
        $noLocation = new Response(status: 302, headers: FieldMap::default());
        $inner = self::fakeClient($noLocation);
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(302, $tx->response->status);
    }

    public function testMultipleLocationHeadersNotFollowed(): void
    {
        $response = new Response(status: 302, headers: FieldMap::from([
            ['location', 'http://example.com/a'],
            ['location', 'http://example.com/b'],
        ]));
        $inner = self::fakeClient($response);
        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(302, $tx->response->status);
    }

    public function testTooManyRedirectsThrows(): void
    {
        $responses = [];
        for ($i = 0; $i < 15; $i++) {
            $responses[] = self::redirect(302, 'http://example.com/loop');
        }

        $inner = self::fakeClient(...$responses);
        $client = new RedirectClient($inner, maxRedirects: 5);

        $this->expectException(TooManyRedirectsException::class);
        $client->send(self::request());
    }

    public function testMultipleRedirects(): void
    {
        $inner = self::fakeClient(
            self::redirect(302, 'http://example.com/a'),
            self::redirect(302, 'http://example.com/b'),
            self::redirect(302, 'http://example.com/c'),
            self::ok(),
        );

        $client = new RedirectClient($inner);
        $tx = $client->send(self::request());

        static::assertSame(200, $tx->response->status);
        static::assertCount(4, $inner->requests);
    }

    public function testInvalidLocationThrowsProtocolException(): void
    {
        $response = new Response(status: 302, headers: FieldMap::from([['location', 'ht tp://bad url']]));
        $inner = self::fakeClient($response);
        $client = new RedirectClient($inner);

        $this->expectException(ProtocolException::class);
        $client->send(self::request());
    }

    public function testRedirectDiscardsResponseBody(): void
    {
        $body = new IO\MemoryHandle('redirect body content');
        $redirectResponse = new Response(
            status: 302,
            headers: FieldMap::from([['location', 'http://example.com/new']]),
            body: $body,
        );
        $inner = self::fakeClient($redirectResponse, self::ok());
        $client = new RedirectClient($inner);

        $client->send(self::request());

        static::assertTrue($body->reachedEndOfDataSource());
    }
}
