<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;
use Psl\Network;
use Psl\Network\Exception\AlreadyStoppedException;
use Psl\TCP;

final class ServerTest extends TestCase
{
    public function testNextConnectionOnStoppedServer(): void
    {
        $server = TCP\Server::create(
            '127.0.0.1',
            0,
            TCP\ServerOptions::create()->withNoDelay(
                true,
            )->withSocketOptions(Network\SocketOptions::create()->withAddressReuse(false)->withPortReuse(
                false,
            )->withBroadcast(true)),
        );

        $server->close();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->nextConnection();
    }

    public function testGetLocalAddressOnStoppedServer(): void
    {
        $server = TCP\Server::create('127.0.0.1');
        $server->close();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->getLocalAddress();
    }

    public function testWaitsForPendingOperation(): void
    {
        $server = TCP\Server::create('127.0.0.1');

        $first = Async\run(static fn() => $server->nextConnection());

        [$second_connection, $client_one, $client_two] = Async\concurrently([
            static fn() => $server->nextConnection(),
            static fn() => TCP\connect('127.0.0.1', $server->getLocalAddress()->port),
            static fn() => TCP\connect('127.0.0.1', $server->getLocalAddress()->port),
        ]);

        static::assertTrue($first->isComplete());
        $first_connection = $first->await();

        $client_two->write('hello');
        $pocket = $second_connection->read(5);

        static::assertSame('hello', $pocket);

        $client_one->close();
        $client_two->close();
        $first_connection->close();
        $second_connection->close();

        $server->close();
    }

    public function testIncoming(): void
    {
        $server = TCP\Server::create('127.0.0.1');
        $incoming = $server->incoming();
        Async\Scheduler::delay(DateTime\Duration::milliseconds(1), static fn() => $server->close());
        Async\Scheduler::defer(static function () use ($server) {
            TCP\connect('127.0.0.1', $server->getLocalAddress()->port);
        });

        $connections = [];
        foreach ($incoming as $connection) {
            $connections[] = $connection;
        }
        static::assertCount(1, $connections);
    }

    public function testAccessUnderlyingStream(): void
    {
        $server = TCP\Server::create('127.0.0.1');
        $stream = $server->getStream();
        $deferred = new Async\Deferred();
        $watcher = Async\Scheduler::onReadable($stream, static fn() => $deferred->complete(true));
        $client = TCP\connect('127.0.0.1', $server->getLocalAddress()->port);

        $deferred->getAwaitable()->await();

        static::assertTrue($deferred->isComplete());

        Async\Scheduler::cancel($watcher);
        $connection = $server->nextConnection();
        $client->write('hello');

        static::assertSame('hello', $connection->read(5));

        $client->close();
        $server->close();
    }
}
