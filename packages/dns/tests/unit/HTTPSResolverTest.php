<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Binary\Reader;
use Psl\Binary\Writer;
use Psl\DateTime\Duration;
use Psl\DNS\Exception\NetworkException;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\HTTPSResolver;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResponseCode;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientInterface;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Str;
use Psl\TCP;
use ReflectionClass;
use RuntimeException;

/**
 * @mago-expect lint:excessive-nesting
 */
final class HTTPSResolverTest extends TestCase
{
    public function testQueryReturnsARecord(): void
    {
        [$port, $serverFuture] = self::startDoHServer(static function (string $dnsQuery): string {
            $id = new Reader($dnsQuery)->u16();

            $answerName = "\x07example\x03com\x00";

            return new Writer()
                ->u16($id)
                ->u16(0x8180) // response, recursion desired + available
                ->u16(0) // qdcount
                ->u16(1) // ancount
                ->u16(0) // nscount
                ->u16(0) // arcount
                ->bytes($answerName)
                ->u16(1) // A
                ->u16(1) // IN
                ->u32(300) // TTL
                ->u16(4) // rdlength
                ->u8(93)
                ->u8(184)
                ->u8(216)
                ->u8(34) // 93.184.216.34
                ->toString();
        });

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");
            $response = $resolver->query('example.com', RecordType::A);

            static::assertSame(ResponseCode::NoError, $response->code);
            static::assertCount(1, $response->answers);
            static::assertInstanceOf(ARecord::class, $response->answers[0]);
            static::assertSame('93.184.216.34', $response->answers[0]->address->toString());
        } finally {
            $serverFuture->await();
        }
    }

    public function testRequestIncludesContentLength(): void
    {
        $receivedContentLength = null;

        [$port, $serverFuture] = self::startDoHServer(static function (string $dnsQuery) use (
            &$receivedContentLength,
        ): string {
            $id = new Reader($dnsQuery)->u16();

            $answerName = "\x07example\x03com\x00";

            return new Writer()
                ->u16($id)
                ->u16(0x8180)
                ->u16(0)
                ->u16(1)
                ->u16(0)
                ->u16(0)
                ->bytes($answerName)
                ->u16(1)
                ->u16(1)
                ->u32(300)
                ->u16(4)
                ->u8(10)
                ->u8(0)
                ->u8(0)
                ->u8(1)
                ->toString();
        }, headerCapture: $receivedContentLength);

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");
            $resolver->query('example.com', RecordType::A);

            static::assertNotNull($receivedContentLength, 'content-length header must be present');
            static::assertGreaterThan(0, $receivedContentLength, 'content-length must be > 0');
        } finally {
            $serverFuture->await();
        }
    }

    public function testNon200ResponseThrowsNetworkException(): void
    {
        [$port, $serverFuture] = self::startDoHServer(
            handler: null,
            statusCode: 503,
            statusText: 'Service Unavailable',
        );

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");

            $this->expectException(NetworkException::class);
            $this->expectExceptionMessageMatches('/HTTP 503/');
            $resolver->query('example.com', RecordType::A);
        } finally {
            $serverFuture->await();
        }
    }

    public function testIdMismatchThrowsProtocolException(): void
    {
        [$port, $serverFuture] = self::startDoHServer(static function (string $dnsQuery): string {
            $id = new Reader($dnsQuery)->u16();
            $wrongId = ($id + 1) & 0xFFFF;

            $answerName = "\x07example\x03com\x00";

            return new Writer()
                ->u16($wrongId)
                ->u16(0x8180)
                ->u16(0)
                ->u16(1)
                ->u16(0)
                ->u16(0)
                ->bytes($answerName)
                ->u16(1)
                ->u16(1)
                ->u32(300)
                ->u16(4)
                ->u8(10)
                ->u8(0)
                ->u8(0)
                ->u8(1)
                ->toString();
        });

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");

            $this->expectException(ProtocolException::class);
            $this->expectExceptionMessageMatches('/does not match query ID/');
            $resolver->query('example.com', RecordType::A);
        } finally {
            $serverFuture->await();
        }
    }

    public function testConcurrentQueries(): void
    {
        $requestCount = 0;

        [$port, $serverFuture] = self::startDoHServer(static function (string $dnsQuery) use (&$requestCount): string {
            $requestCount++;
            $id = new Reader($dnsQuery)->u16();

            $answerName = "\x07example\x03com\x00";

            return new Writer()
                ->u16($id)
                ->u16(0x8180)
                ->u16(0)
                ->u16(1)
                ->u16(0)
                ->u16(0)
                ->bytes($answerName)
                ->u16(1)
                ->u16(1)
                ->u32(300)
                ->u16(4)
                ->u8(10)
                ->u8(0)
                ->u8(0)
                ->u8((int) ($requestCount & 0xFF))
                ->toString();
        }, maxRequests: 3);

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");

            $results = Async\concurrently([
                static fn() => $resolver->query('example.com', RecordType::A),
                static fn() => $resolver->query('example.com', RecordType::A),
                static fn() => $resolver->query('example.com', RecordType::A),
            ]);

            static::assertCount(3, $results);
            foreach ($results as $response) {
                static::assertSame(ResponseCode::NoError, $response->code);
                static::assertCount(1, $response->answers);
                static::assertInstanceOf(ARecord::class, $response->answers[0]);
            }
        } finally {
            $serverFuture->await();
        }
    }

    public function testDefaultDnssecIsFalse(): void
    {
        $resolver = new HTTPSResolver('https://1.1.1.1/dns-query');

        $reflection = new ReflectionClass($resolver);
        $dnssec = $reflection->getProperty('dnssec')->getValue($resolver);

        static::assertFalse($dnssec);
    }

    public function testCustomDnssecIsRespected(): void
    {
        $resolver = new HTTPSResolver('https://1.1.1.1/dns-query', dnssec: true);

        $reflection = new ReflectionClass($resolver);
        $dnssec = $reflection->getProperty('dnssec')->getValue($resolver);

        static::assertTrue($dnssec);
    }

    public function testNullClientCreatesDefault(): void
    {
        $resolver = new HTTPSResolver('https://1.1.1.1/dns-query');

        $reflection = new ReflectionClass($resolver);
        $client = $reflection->getProperty('client')->getValue($resolver);

        static::assertInstanceOf(Client::class, $client);
    }

    public function testCustomClientPropertyIsStored(): void
    {
        $customClient = new Client();
        $resolver = new HTTPSResolver('https://1.1.1.1/dns-query', $customClient);

        $reflection = new ReflectionClass($resolver);
        $client = $reflection->getProperty('client')->getValue($resolver);

        static::assertSame($customClient, $client);
    }

    public function testCustomClientIsNotReplacedByDefault(): void
    {
        $marker = new class implements ClientInterface {
            public function send(
                Request $request,
                SendConfiguration $configuration = new SendConfiguration(),
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                throw new RuntimeException('should not be called');
            }
        };

        $resolver = new HTTPSResolver('https://1.1.1.1/dns-query', $marker);

        $reflection = new ReflectionClass($resolver);
        $client = $reflection->getProperty('client')->getValue($resolver);

        static::assertSame($marker, $client);
        static::assertNotInstanceOf(Client::class, $client);
    }

    public function testNullClientDoesNotOverrideExplicitClient(): void
    {
        $client1 = new Client();
        $client2 = new Client();

        $resolver1 = new HTTPSResolver('https://1.1.1.1/dns-query', $client1);
        $resolver2 = new HTTPSResolver('https://1.1.1.1/dns-query', $client2);

        $reflection = new ReflectionClass($resolver1);

        $stored1 = $reflection->getProperty('client')->getValue($resolver1);
        $stored2 = $reflection->getProperty('client')->getValue($resolver2);

        static::assertSame($client1, $stored1);
        static::assertSame($client2, $stored2);
        static::assertNotSame($stored1, $stored2);
    }

    public function testEmptyBodyResponseThrows(): void
    {
        [$port, $serverFuture] = self::startDoHServerWithEmptyBody();

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");

            $this->expectException(ProtocolException::class);
            $resolver->query('example.com', RecordType::A);
        } finally {
            $serverFuture->await();
        }
    }

    public function testRequestIncludesContentTypeHeader(): void
    {
        $receivedContentType = null;

        [$port, $serverFuture] = self::startDoHServerWithContentTypeCapture(static function (string $dnsQuery): string {
            $id = new Reader($dnsQuery)->u16();

            $answerName = "\x07example\x03com\x00";

            return new Writer()
                ->u16($id)
                ->u16(0x8180)
                ->u16(0)
                ->u16(1)
                ->u16(0)
                ->u16(0)
                ->bytes($answerName)
                ->u16(1)
                ->u16(1)
                ->u32(300)
                ->u16(4)
                ->u8(10)
                ->u8(0)
                ->u8(0)
                ->u8(1)
                ->toString();
        }, $receivedContentType);

        try {
            $resolver = new HTTPSResolver("http://127.0.0.1:{$port}/dns-query");
            $resolver->query('example.com', RecordType::A);

            static::assertSame('application/dns-message', $receivedContentType);
        } finally {
            $serverFuture->await();
        }
    }

    /**
     * @param Closure(string): string $handler
     *
     * @return array{int<0, 65535>, Async\Awaitable<void>}
     */
    private static function startDoHServerWithContentTypeCapture(
        Closure $handler,
        null|string &$contentTypeCapture = null,
    ): array {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run(static function () use ($listener, $handler, &$contentTypeCapture): void {
            try {
                $conn = $listener->accept(new Async\TimeoutCancellationToken(Duration::seconds(5)));
                $reader = new IO\Reader($conn);

                $contentLength = 0;
                while (true) {
                    $line = $reader->readLine();
                    if ($line === null || $line === '') {
                        break;
                    }

                    if (Str\Byte\starts_with_ci($line, 'content-type:')) {
                        $contentTypeCapture = Str\Byte\trim(Str\Byte\after($line, ':') ?? '');
                    }

                    if (Str\Byte\starts_with_ci($line, 'content-length:')) {
                        $contentLength = (int) Str\Byte\trim(Str\Byte\after($line, ':') ?? '');
                    }
                }

                $body = $reader->readFixedSize($contentLength);
                $responseBody = $handler($body);
                $headers =
                    "HTTP/1.1 200 OK\r\nContent-Type: application/dns-message\r\nContent-Length: "
                    . Str\Byte\length($responseBody)
                    . "\r\nConnection: close\r\n\r\n";
                $conn->writeAll($headers . $responseBody);
                $conn->close();
            } catch (IO\Exception\ExceptionInterface|Async\Exception\CancelledException) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }
        });

        return [$port, $future];
    }

    /**
     * @param null|(Closure(string): string) $handler
     *
     * @return array{int<0, 65535>, Async\Awaitable<void>}
     */
    private static function startDoHServer(
        null|Closure $handler = null,
        int $statusCode = 200,
        string $statusText = 'OK',
        int $maxRequests = 1,
        null|int &$headerCapture = null,
    ): array {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run(static function () use (
            $listener,
            $handler,
            $statusCode,
            $statusText,
            $maxRequests,
            &$headerCapture,
        ): void {
            try {
                for ($i = 0; $i < $maxRequests; $i++) {
                    $conn = $listener->accept(new Async\TimeoutCancellationToken(Duration::seconds(5)));
                    $reader = new IO\Reader($conn);

                    $contentLength = 0;
                    while (true) {
                        $line = $reader->readLine();
                        if ($line === null || $line === '') {
                            break;
                        }

                        if (Str\Byte\starts_with_ci($line, 'content-length:')) {
                            $contentLength = (int) Str\Byte\trim(Str\Byte\after($line, ':') ?? '');
                            $headerCapture = $contentLength;
                        }
                    }

                    $body = $reader->readFixedSize($contentLength);
                    if ($handler !== null && $statusCode === 200) {
                        $responseBody = $handler($body);
                        $headers =
                            "HTTP/1.1 200 OK\r\nContent-Type: application/dns-message\r\nContent-Length: "
                            . Str\Byte\length($responseBody)
                            . "\r\nConnection: close\r\n\r\n";
                        $conn->writeAll($headers . $responseBody);
                    } else {
                        $conn->writeAll(
                            "HTTP/1.1 {$statusCode} {$statusText}\r\nContent-Length: 0\r\nConnection: close\r\n\r\n",
                        );
                    }

                    $conn->close();
                }
            } catch (IO\Exception\ExceptionInterface|Async\Exception\CancelledException) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }
        });

        return [$port, $future];
    }

    /**
     * @return array{int<0, 65535>, Async\Awaitable<void>}
     */
    private static function startDoHServerWithEmptyBody(): array
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run(static function () use ($listener): void {
            try {
                $conn = $listener->accept(new Async\TimeoutCancellationToken(Duration::seconds(5)));
                $reader = new IO\Reader($conn);

                $contentLength = 0;
                while (true) {
                    $line = $reader->readLine();
                    if ($line === null || $line === '') {
                        break;
                    }

                    if (Str\Byte\starts_with_ci($line, 'content-length:')) {
                        $contentLength = (int) Str\Byte\trim(Str\Byte\after($line, ':') ?? '');
                    }
                }

                $reader->readFixedSize($contentLength);
                $conn->writeAll("HTTP/1.1 200 OK\r\nContent-Length: 0\r\nConnection: close\r\n\r\n");
                $conn->close();
            } catch (IO\Exception\ExceptionInterface|Async\Exception\CancelledException) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }
        });

        return [$port, $future];
    }
}
