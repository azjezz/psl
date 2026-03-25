<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H1\Transport;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\URL;

use function chr;
use function str_repeat;
use function str_split;
use function strlen;
use function strpos;
use function substr;

final class MisbehavingServerTest extends TestCase
{
    /**
     * @param callable(TCP\StreamInterface): void $serverHandler
     *
     * @return array{Transaction, bool}
     */
    private static function exchangeWithServer(
        callable $serverHandler,
        null|Request $request = null,
        int $maxHeaderSize = 8192,
    ): array {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $address = $listener->getLocalAddress();

        $url = URL\parse("http://127.0.0.1:{$address->port}/");
        $request ??= new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        if ($request->url === null) {
            $request = $request->withUrl($url);
        }

        if ($request->requestTarget === '') {
            $request = $request->withRequestTarget('/');
        }

        $serverFuture = Async\run(static function () use ($listener, $serverHandler): void {
            try {
                $conn = $listener->accept();
                $serverHandler($conn);
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new TCP\Connector(new TCP\ConnectConfiguration(noDelay: true));
            /** @var int<0, 65535> $port */
            $port = $address->port;
            $stream = $connector->connect('127.0.0.1', $port, new TimeoutCancellationToken(Duration::seconds(5)));
            $connection = new H1Connection($stream);

            /** @var positive-int $maxHeaderSize */
            return Transport::exchange(
                $connection,
                $request,
                $request->url ?? $url,
                $maxHeaderSize,
                cancellation: new TimeoutCancellationToken(Duration::seconds(5)),
            );
        } finally {
            $listener->close();
            try {
                $serverFuture->await();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    public function testNormalResponseOverTcp(): void
    {
        [$transaction, $keepAlive] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        static::assertSame(200, $transaction->response->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('ok', $body->readAll());
    }

    public function testServerClosesConnectionBeforeResponse(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->close();
        });
    }

    public function testServerSendsGarbage(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("\x00\x01\x02\x03\x04\x05garbage\xff\xfe\r\n\r\n");
            $conn->close();
        });
    }

    public function testServerSendsOnlyNullBytes(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll(str_repeat("\x00", 100) . "\r\n\r\n");
            $conn->close();
        });
    }

    public function testServerSendsOversizedStatusLine(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll('HTTP/1.1 200 ' . str_repeat('X', 1024) . "\r\n\r\n");
            $conn->close();
        }, maxHeaderSize: 256);
    }

    public function testServerSendsOversizedHeaders(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $headers = '';
            for ($i = 0; $i < 100; $i++) {
                $headers .= "x-header-{$i}: " . str_repeat('V', 100) . "\r\n";
            }

            $conn->writeAll("HTTP/1.1 200 OK\r\n{$headers}\r\n");
            $conn->close();
        }, maxHeaderSize: 512);
    }

    public function testServerSendsBrokenChunkedEncoding(): void
    {
        $this->expectException(ProtocolException::class);

        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\nZZZ\r\nbad\r\n");
            $conn->close();
        });

        $transaction->response->body?->readAll();
    }

    public function testServerDropsConnectionMidChunkedBody(): void
    {
        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\na\r\nhello");
            $conn->shutdown();
            $conn->close();
        });

        $body = $transaction->response->body;
        static::assertNotNull($body);
        $data = $body->readAll();
        static::assertSame('hello', $data);
    }

    public function testServerSendsContentLengthThenClosesEarly(): void
    {
        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 1000\r\n\r\nshort");
            $conn->shutdown();
            $conn->close();
        });

        $body = $transaction->response->body;
        static::assertNotNull($body);
        $data = $body->readAll();
        static::assertSame('short', $data);
    }

    public function testServerSendsBinaryBody(): void
    {
        $binary = '';
        for ($i = 0; $i < 256; $i++) {
            $binary .= chr($i);
        }

        $len = strlen($binary);

        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn) use (
            $binary,
            $len,
        ): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: {$len}\r\n\r\n{$binary}");
            $conn->close();
        });

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame($binary, $body->readAll());
    }

    public function testServerSendsResponseInTinyChunks(): void
    {
        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $full = "HTTP/1.1 200 OK\r\ncontent-length: 5\r\n\r\nhello";
            foreach (str_split($full, 1) as $byte) {
                $conn->writeAll($byte);
            }

            $conn->close();
        });

        static::assertSame(200, $transaction->response->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('hello', $body->readAll());
    }

    public function testServerSendsMultipleInformationalThenFinal(): void
    {
        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 100 Continue\r\n\r\n");
            $conn->writeAll("HTTP/1.1 102 Processing\r\n\r\n");
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 4\r\n\r\ndone");
            $conn->close();
        });

        static::assertSame(200, $transaction->response->status);
        static::assertCount(2, $transaction->informational);
        static::assertSame(100, $transaction->informational[0]->status);
        static::assertSame(102, $transaction->informational[1]->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('done', $body->readAll());
    }

    public function testServerSendsLargeBody(): void
    {
        $body = str_repeat('ABCDEFGHIJ', 10_000);
        $len = strlen($body);

        [$transaction, $_] = self::exchangeWithServer(static function (TCP\StreamInterface $conn) use (
            $body,
            $len,
        ): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: {$len}\r\n\r\n{$body}");
            $conn->close();
        });

        $body = $transaction->response->body;
        static::assertNotNull($body);
        $responseBody = $body->readAll();
        static::assertSame($len, strlen($responseBody));
    }

    public function testPostBodyIsReceivedByServer(): void
    {
        $requestBody = 'test payload data';
        $url = URL\parse('http://127.0.0.1:1/submit');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/submit',
            headers: FieldMap::from([
                ['content-type', 'text/plain'],
                ['content-length', (string) strlen($requestBody)],
            ]),
            body: new IO\MemoryHandle($requestBody),
        );

        [$transaction, $_] = self::exchangeWithServer(
            static function (TCP\StreamInterface $conn): void {
                $data = '';
                while (true) {
                    $chunk = $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
                    if ($chunk === '') {
                        break;
                    }

                    $data .= $chunk;
                    $separatorPos = strpos($data, "\r\n\r\n");
                    if ($separatorPos !== false) {
                        $bodyStart = $separatorPos + 4;
                        $receivedBody = substr($data, $bodyStart);
                        if (strlen($receivedBody) >= 17) {
                            break;
                        }
                    }
                }

                /** @var int<0, max> $separatorEnd */
                $separatorEnd = strpos($data, "\r\n\r\n");
                $bodyStart = $separatorEnd + 4;
                $receivedBody = substr($data, $bodyStart);
                $responseBody = 'echo:' . $receivedBody;
                $conn->writeAll(
                    "HTTP/1.1 200 OK\r\ncontent-length: " . strlen($responseBody) . "\r\n\r\n{$responseBody}",
                );
                $conn->close();
            },
            $request,
        );

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('echo:test payload data', $body->readAll());
    }

    public function testServerSendsHeaderWithEmptyName(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Empty header name');

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\n: bad\r\ncontent-length: 0\r\n\r\n");
            $conn->close();
        });
    }

    public function testServerSendsHeaderWithoutColon(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Invalid header line');

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.1 200 OK\r\nnocolon\r\n\r\n");
            $conn->close();
        });
    }

    public function testServerSendsHttp10(): void
    {
        [$transaction, $keepAlive] = self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/1.0 200 OK\r\ncontent-length: 3\r\n\r\nfoo");
            $conn->close();
        });

        static::assertFalse($keepAlive);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('foo', $body->readAll());
    }

    public function testServerSendsUnsupportedVersion(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unsupported HTTP version');

        self::exchangeWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(1)));
            $conn->writeAll("HTTP/3.0 200 OK\r\n\r\n");
            $conn->close();
        });
    }
}
