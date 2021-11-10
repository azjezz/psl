<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Network;
use Psl\Network\Exception\AlreadyStoppedException;
use Psl\TCP;
use Throwable;

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

    public function testThrowsForPendingOperation(): void
    {
        $server = TCP\Server::create('127.0.0.1');

        $first = Async\run(static fn() => $server->nextConnection());
        $second = Async\run(static fn() => $server->nextConnection());

        try {
            $second->await();
        } catch (Throwable $exception) {
            static::assertInstanceOf(Network\Exception\RuntimeException::class, $exception);
            static::assertStringContainsStringIgnoringCase('pending', $exception->getMessage());
        }

        $first->ignore();
        $second->ignore();

        $server->stopListening();
    }
}
