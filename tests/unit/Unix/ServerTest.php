<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Filesystem;
use Psl\Network\Exception;
use Psl\Unix;
use Throwable;

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

    public function testThrowsForPendingOperation(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);

        $first = Async\run(static fn() => $server->nextConnection());
        $second = Async\run(static fn() => $server->nextConnection());

        try {
            $second->await();
        } catch (Throwable $exception) {
            static::assertInstanceOf(Exception\RuntimeException::class, $exception);
            static::assertStringContainsStringIgnoringCase('pending', $exception->getMessage());
        }

        $first->ignore();
        $second->ignore();

        $server->stopListening();
    }
}
