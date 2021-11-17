<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Filesystem;
use Psl\Network\Exception;
use Psl\Unix;

use const PHP_OS_FAMILY;

final class ServerTest extends TestCase
{
    public function testNextConnectionOnStoppedServer(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);
        $server->stopListening();

        $this->expectException(Exception\AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->nextConnection();
    }

    public function testGetLocalAddressOnStoppedServer(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);
        $server->stopListening();

        $this->expectException(Exception\AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->getLocalAddress();
    }

    public function testWaitsForPendingOperation(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);

        $first = Async\run(static fn() => $server->nextConnection());

        [$second_connection, $client_one, $client_two] = Async\concurrent([
            static fn() => $server->nextConnection(),
            static fn() => Unix\connect($sock),
            static fn() => Unix\connect($sock),
        ]);

        static::assertTrue($first->isComplete());

        $client_two->write('hello');
        $pocket = $second_connection->read(5);

        static::assertSame('hello', $pocket);

        $client_one->close();
        $client_two->close();
        $second_connection->close();

        $server->stopListening();
    }
}
