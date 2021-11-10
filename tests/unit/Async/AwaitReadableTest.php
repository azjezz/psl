<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;

use function fwrite;
use function stream_socket_pair;

use const PHP_OS_FAMILY;
use const STREAM_IPPROTO_IP;
use const STREAM_PF_INET;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

final class AwaitReadableTest extends TestCase
{
    public function testAwaitReadable(): void
    {
        $domain = PHP_OS_FAMILY === 'Windows' ? STREAM_PF_INET : STREAM_PF_UNIX;
        $sockets = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $write_socket = $sockets[0];
        $read_socket = $sockets[1];

        $ref = new Psl\Ref('');
        $handles = [
            Async\run(static function () use ($ref, $read_socket) {
                $ref->value .= '[read:waiting]';

                Async\await_readable($read_socket);

                $ref->value .= '[read:ready]';
            }),
            Async\run(static function () use ($ref, $write_socket) {
                $ref->value .= '[write:sleep]';

                Async\sleep(0.001);

                fwrite($write_socket, "hello", 5);

                $ref->value .= '[write:done]';
            }),
        ];

        Async\all($handles);

        static::assertSame('[read:waiting][write:sleep][write:done][read:ready]', $ref->value);
    }
}
