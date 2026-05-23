<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Connection;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\URL;

final class PooledConnectorTunnelTest extends TestCase
{
    public function testHttpTargetUsesForwardProxyNotConnect(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $address = $listener->getLocalAddress();

        /** @var int<0, 65535> $tunnelPort */
        $tunnelPort = $address->port;

        $tunnelFuture = Async\run(static function () use ($listener): void {
            try {
                $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new PooledConnector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(URL\parse("http://127.0.0.1:{$tunnelPort}")),
            );

            $request = new Request(method: 'GET', url: URL\parse('http://target.example.com:8080/'));
            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertTrue($connection->isForwardProxy);
        } finally {
            $listener->close();
            try {
                $tunnelFuture->await();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

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
                proxyConfiguration: new ProxyConfiguration(
                    URL\parse("http://127.0.0.1:{$tunnelPort}"),
                    skipProxyFor: ['127.0.0.1'],
                ),
            );

            $request = new Request(method: 'GET', url: URL\parse("http://127.0.0.1:{$targetPort}/"));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
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
            'The tunnel server should NOT have been contacted when the host is in the skipProxyFor list',
        );
    }

    public function testForwardProxyUsedForNonBypassedHost(): void
    {
        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        $proxyAddress = $proxyListener->getLocalAddress();

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyAddress->port;

        $tunnelFuture = Async\run(static function () use ($proxyListener): void {
            try {
                $conn = $proxyListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new PooledConnector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(
                    URL\parse("http://127.0.0.1:{$proxyPort}"),
                    skipProxyFor: ['other.host'],
                ),
            );

            $request = new Request(method: 'GET', url: URL\parse('http://target.example.com:9090/'));
            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertTrue($connection->isForwardProxy);
        } finally {
            $proxyListener->close();
            try {
                $tunnelFuture->await();
            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }
}
