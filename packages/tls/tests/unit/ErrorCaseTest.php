<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Network;
use Psl\TCP;
use Psl\TLS;
use Psl\TLS\Exception\HandshakeFailedException;

final class ErrorCaseTest extends TestCase
{
    public function testLazyAcceptorThrowsOnNonTlsData(): void
    {
        $this->expectException(HandshakeFailedException::class);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $lazy = new TLS\LazyAcceptor();
                // This should throw because the client sends plain text, not TLS
                $lazy->accept($connection);
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                // Send non-TLS data
                $client->writeAll('NOT-A-TLS-HANDSHAKE');
                $client->close();
            },
        ]);

        $listener->close();
    }

    public function testConnectorThrowsOnClosedStream(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource is not available.');

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                Async\sleep(Duration::milliseconds(50));
                $connection->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                $client->close();

                $connector = new TLS\Connector(new TLS\ClientConfiguration());
                // Trying to TLS-connect on a closed stream should throw
                $connector->connect($client);
            },
        ]);
    }

    public function testAcceptorThrowsOnClosedStream(): void
    {
        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource is not available.');

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port ?? 0;

        Async\concurrently([
            'server' => static function () use ($listener): void {
                $connection = $listener->accept();
                $connection->close();

                $acceptor = new TLS\Acceptor(TLS\ServerConfiguration::create(TLS\Certificate::create(
                    '/dev/null',
                    '/dev/null',
                )));
                // Trying to accept TLS on a closed stream should throw
                $acceptor->accept($connection);
            },
            'client' => static function () use ($port): void {
                $client = TCP\connect('127.0.0.1', $port);
                Async\sleep(Duration::milliseconds(50));
                $client->close();
            },
        ]);

        $listener->close();
    }
}
