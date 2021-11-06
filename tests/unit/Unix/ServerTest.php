<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Filesystem;
use Psl\Network\Exception\AlreadyStoppedException;
use Psl\Unix;

final class ServerTest extends TestCase
{
    public function testNextConnectionOnStoppedServer(): void
    {
        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);
        $server->stopListening();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->nextConnection();
    }

    public function testGetLocalAddressOnStoppedServer(): void
    {
        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . ".sock";
        $server = Unix\Server::create($sock);
        $server->stopListening();

        $this->expectException(AlreadyStoppedException::class);
        $this->expectExceptionMessage('Server socket has already been stopped.');

        $server->getLocalAddress();
    }
}
