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
        $listener = TCP\listen('127.0.0.1', 0, no_delay: true, reuse_address: false, reuse_port: false);

        $listener->close();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $listener->accept();
    }

    public function testGetLocalAddressOnStoppedListener(): void
    {
        $listener = TCP\listen('127.0.0.1');
        $listener->close();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $listener->getLocalAddress();
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
        $first_connection = $first->await();

        $client_two->write('hello');
        $pocket = $second_connection->read(5);

        static::assertSame('hello', $pocket);

        $client_one->close();
        $client_two->close();
        $first_connection->close();
        $second_connection->close();

        $listener->close();
    }
}
