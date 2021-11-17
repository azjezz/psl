<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
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
            TCP\ServerOptions::create()
                ->withNoDelay(true)
                ->withSocketOptions(
                    Network\SocketOptions::create()
                        ->withAddressReuse(false)
                        ->withPortReuse(false)
                        ->withBroadcast(true)
                )
        );

        $server->stopListening();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->nextConnection();
    }

    public function testGetLocalAddressOnStoppedServer(): void
    {
        $server = TCP\Server::create('127.0.0.1');
        $server->stopListening();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->getLocalAddress();
    }

    public function testWaitsForPendingOperation(): void
    {
        $server = TCP\Server::create('127.0.0.1');

        $first = Async\run(static fn() => $server->nextConnection());

        [$second_connection, $client_one, $client_two] = Async\concurrent([
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

        $server->stopListening();
    }
}
