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
    public function testAcceptOnStoppedListener(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, noDelay: true, reuseAddress: false, reusePort: false);

        $listener->close();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $listener->accept();
    }

    public function testGetLocalAddressOnStoppedListener(): void
    {
        $listener = TCP\listen('127.0.0.1');
        $address = $listener->getLocalAddress();
        $listener->close();

        // getLocalAddress() returns the cached address even after the listener is stopped.
        $addressAfterClose = $listener->getLocalAddress();
        static::assertSame($address->host, $addressAfterClose->host);
        static::assertSame($address->port, $addressAfterClose->port);
    }

    public function testListenWithCustomBacklog(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, backlog: 128);
        $address = $listener->getLocalAddress();

        static::assertSame('127.0.0.1', $address->host);
        static::assertGreaterThan(0, $address->port);

        $client = TCP\connect('127.0.0.1', $address->port);
        $server = $listener->accept();

        $client->write('ping');
        static::assertSame('ping', $server->read(4));

        $client->close();
        $server->close();
        $listener->close();
    }

    public function testAcceptMultipleConnections(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, noDelay: true, backlog: 64);
        $address = $listener->getLocalAddress();

        [$server1, $client1, $client2] = Async\concurrently([
            $listener->accept(...),
            static fn(): Network\StreamInterface => TCP\connect('127.0.0.1', $address->port),
            static fn(): Network\StreamInterface => TCP\connect('127.0.0.1', $address->port),
        ]);

        $server2 = $listener->accept();

        $client1->write('one');
        $client2->write('two');

        $msg1 = $server1->read(3);
        $msg2 = $server2->read(3);

        static::assertTrue($msg1 === 'one' && $msg2 === 'two' || $msg1 === 'two' && $msg2 === 'one');

        $client1->close();
        $client2->close();
        $server1->close();
        $server2->close();
        $listener->close();
    }

    public function testWaitsForPendingOperation(): void
    {
        $listener = TCP\listen('127.0.0.1');

        $first = Async\run($listener->accept(...));

        [$second_connection, $client_one, $client_two] = Async\concurrently([
            $listener->accept(...),
            static fn(): Network\StreamInterface => TCP\connect('127.0.0.1', $listener->getLocalAddress()->port),
            static fn(): Network\StreamInterface => TCP\connect('127.0.0.1', $listener->getLocalAddress()->port),
        ]);

        static::assertTrue($first->isComplete());
        $firstConnection = $first->await();

        $client_two->write('hello');
        $pocket = $second_connection->read(5);

        static::assertSame('hello', $pocket);

        $client_one->close();
        $client_two->close();
        $firstConnection->close();
        $second_connection->close();

        $listener->close();
    }
}
