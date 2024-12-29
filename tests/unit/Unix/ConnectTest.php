<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Filesystem;
use Psl\OS;
use Psl\Str;
use Psl\Unix;

final class ConnectTest extends TestCase
{
    public function testConnect(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Unix Server is not supported on Windows platform.');
        }

        $sock = Filesystem\create_temporary_file(prefix: 'psl-examples') . '.sock';

        Async\concurrently([
            'server' => static function () use ($sock): void {
                $server = Unix\Server::create($sock);
                self::assertSame("unix://{$sock}", $server->getLocalAddress()->toString());
                $connection = $server->nextConnection();
                $request = $connection->read();
                self::assertSame('Hello, World!', $request);
                $connection->writeAll(Str\reverse($request));
                $connection->close();
                $server->close();
            },
            'client' => static function () use ($sock): void {
                $client = Unix\connect($sock);
                self::assertSame('unix://' . $sock, $client->getPeerAddress()->toString());
                $client->writeAll('Hello, World!');
                $response = $client->readAll();
                self::assertSame('!dlroW ,olleH', $response);
                $client->close();
            },
        ]);
    }
}
