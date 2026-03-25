<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Connection;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\IO\Reader;
use Psl\Network;
use Psl\TCP;

use function Psl\URL\parse;

final class PooledConnectorTunnelTest extends TestCase
{
    /**
     * Verify that PooledConnector sends an HTTP CONNECT request to the tunnel
     * server when a tunnel address is configured. We intentionally fail the
     * tunnel handshake (respond 403) so the connector raises a ProtocolException
     * — the goal is only to confirm that the CONNECT line was sent correctly.
     */
    public function testTcpConnectionSendsConnectToTunnel(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $address = $listener->getLocalAddress();

        /** @var int<0, 65535> $tunnelPort */
        $tunnelPort = $address->port;

        $receivedConnectLine = null;

        $tunnelFuture = Async\run(static function () use ($listener, &$receivedConnectLine): void {
            try {
                $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $reader = new Reader($conn);

                while (true) {
                    $line = $reader->readLine();
                    if ($line === null || $line === '') {
                        break;
                    }

                    $receivedConnectLine ??= $line;
                }

                // Respond with 403 so the connector aborts cleanly
                $conn->writeAll("HTTP/1.1 403 Forbidden\r\nContent-Length: 0\r\n\r\n");
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new PooledConnector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                tunnel: "http://127.0.0.1:{$tunnelPort}",
            );

            $request = new Request(method: 'GET', url: parse('http://target.example.com:8080/'));

            try {
                $connector->connect($request, $configuration, new TimeoutCancellationToken(Duration::seconds(5)));
                static::fail('Expected ProtocolException for 403 tunnel response');
            } catch (ProtocolException) {
                static::addToAssertionCount(1);
            }

            static::assertSame(
                'CONNECT target.example.com:8080 HTTP/1.1',
                $receivedConnectLine,
                'The tunnel server should have received a CONNECT request for the target host:port',
            );
        } finally {
            $listener->close();
            try {
                $tunnelFuture->await();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    /**
     * Verify that when the target host appears in the noTunneling list, the
     * connection is made directly without going through the tunnel.
     */
    public function testTunnelBypassForNoTunnelingHost(): void
    {
        // Start a tunnel server that should NOT receive any connection.
        $tunnelListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $tunnelAddress = $tunnelListener->getLocalAddress();

        /** @var int<0, 65535> $tunnelPort */
        $tunnelPort = $tunnelAddress->port;

        $tunnelWasContacted = false;

        $tunnelFuture = Async\run(static function () use ($tunnelListener, &$tunnelWasContacted): void {
            try {
                $tunnelListener->accept(new TimeoutCancellationToken(Duration::milliseconds(500)));
                $tunnelWasContacted = true;
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        // Start a direct target server that the connector should reach without the tunnel.
        $targetListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $targetAddress = $targetListener->getLocalAddress();

        /** @var int<0, 65535> $targetPort */
        $targetPort = $targetAddress->port;

        $targetFuture = Async\run(static function () use ($targetListener): void {
            try {
                $conn = $targetListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                // The connector only establishes the TCP connection — no request
                // is sent, so just accept and let the connection close naturally.
                $conn->read(cancellation: new TimeoutCancellationToken(Duration::milliseconds(500)));
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new PooledConnector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                tunnel: "http://127.0.0.1:{$tunnelPort}",
                noTunneling: ['127.0.0.1'],
            );

            $request = new Request(method: 'GET', url: parse("http://127.0.0.1:{$targetPort}/"));

            $connection = $connector->connect(
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertNotNull($connection);
        } finally {
            $tunnelListener->close();
            $targetListener->close();
            try {
                $tunnelFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }

            try {
                $targetFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }

        static::assertFalse(
            $tunnelWasContacted,
            'The tunnel server should NOT have been contacted when the host is in the noTunneling list',
        );
    }

    /**
     * Verify that a host NOT in the noTunneling list still goes through the
     * tunnel even when noTunneling is configured for other hosts.
     */
    public function testTunnelUsedForNonBypassedHost(): void
    {
        $tunnelListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $tunnelAddress = $tunnelListener->getLocalAddress();

        /** @var int<0, 65535> $tunnelPort */
        $tunnelPort = $tunnelAddress->port;

        $receivedConnectLine = null;

        $tunnelFuture = Async\run(static function () use ($tunnelListener, &$receivedConnectLine): void {
            try {
                $conn = $tunnelListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $reader = new Reader($conn);

                while (true) {
                    $line = $reader->readLine();
                    if ($line === null || $line === '') {
                        break;
                    }

                    $receivedConnectLine ??= $line;
                }

                // Respond with 502 to abort cleanly
                $conn->writeAll("HTTP/1.1 502 Bad Gateway\r\nContent-Length: 0\r\n\r\n");
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new PooledConnector();
            // noTunneling contains 'other.host' — target.example.com is NOT bypassed,
            // so the connection MUST go through the tunnel.
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                tunnel: "http://127.0.0.1:{$tunnelPort}",
                noTunneling: ['other.host'],
            );

            $request = new Request(method: 'GET', url: parse('http://target.example.com:9090/'));

            try {
                $connector->connect($request, $configuration, new TimeoutCancellationToken(Duration::seconds(5)));
                static::fail('Expected ProtocolException for 502 tunnel response');
            } catch (ProtocolException) {
                static::addToAssertionCount(1);
            }

            static::assertSame(
                'CONNECT target.example.com:9090 HTTP/1.1',
                $receivedConnectLine,
                'The tunnel server should have received a CONNECT request because target.example.com is not in noTunneling',
            );
        } finally {
            $tunnelListener->close();
            try {
                $tunnelFuture->await();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }
}
