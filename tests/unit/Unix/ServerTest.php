<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Filesystem;
use Psl\Network\Exception;
use Psl\OS;
use Psl\Unix;

final class ServerTest extends TestCase
{
    public function testNextConnectionOnStoppedServer(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';
        $server = Unix\Server::create($sock);
        $server->close();

        $this->expectException(Exception\AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->nextConnection();
    }

    public function testGetLocalAddressOnStoppedServer(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';
        $server = Unix\Server::create($sock);
        $server->close();

        $this->expectException(Exception\AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->getLocalAddress();
    }

    public function testWaitsForPendingOperation(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';
        $server = Unix\Server::create($sock);

        $first = Async\run(static fn() => $server->nextConnection());

        [$second_connection, $client_one, $client_two] = Async\concurrently([
            static fn() => $server->nextConnection(),
            static fn() => Unix\connect($sock),
            static fn() => Unix\connect($sock),
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

    public function testAccessUnderlyingStream(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';
        $server = Unix\Server::create($sock);
        $stream = $server->getStream();
        $deferred = new Async\Deferred();
        $watcher = Async\Scheduler::onReadable($stream, static fn() => $deferred->complete(true));
        $client = Unix\connect($sock);

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
