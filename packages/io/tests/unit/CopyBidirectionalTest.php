<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\IO;
use Psl\OS;

use function stream_socket_pair;
use function stream_socket_shutdown;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_INET;
use const STREAM_PF_UNIX;
use const STREAM_SHUT_WR;
use const STREAM_SOCK_STREAM;

final class CopyBidirectionalTest extends TestCase
{
    public function testBidirectionalCopy(): void
    {
        $domain = OS\is_windows() ? STREAM_PF_INET : STREAM_PF_UNIX;

        // Pair 1: side_a <-> proxy_a
        $pair1 = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        static::assertNotFalse($pair1);
        // Pair 2: side_b <-> proxy_b
        $pair2 = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        static::assertNotFalse($pair2);

        $side_a = new IO\CloseReadWriteStreamHandle($pair1[0]);
        $proxy_a = new IO\CloseReadWriteStreamHandle($pair1[1]);
        $side_b = new IO\CloseReadWriteStreamHandle($pair2[0]);
        $proxy_b = new IO\CloseReadWriteStreamHandle($pair2[1]);

        Async\concurrently::<string, void>([
            'proxy' => static function () use ($proxy_a, $proxy_b): void {
                [$a_to_b, $b_to_a] = IO\copy_bidirectional($proxy_a, $proxy_b);

                // a sent "hello" (5 bytes), b sent "world!" (6 bytes)
                static::assertSame(5, $a_to_b);
                static::assertSame(6, $b_to_a);

                $proxy_a->close();
                $proxy_b->close();
            },
            'side_a' => static function () use ($side_a, $pair1): void {
                $side_a->writeAll('hello');
                // Shut down the write side to signal EOF to the proxy
                stream_socket_shutdown($pair1[0], STREAM_SHUT_WR);
                // Read what b sent via the proxy
                $data = $side_a->readAll();
                static::assertSame('world!', $data);
                $side_a->close();
            },
            'side_b' => static function () use ($side_b, $pair2): void {
                $side_b->writeAll('world!');
                // Shut down the write side to signal EOF to the proxy
                stream_socket_shutdown($pair2[0], STREAM_SHUT_WR);
                // Read what a sent via the proxy
                $data = $side_b->readAll();
                static::assertSame('hello', $data);
                $side_b->close();
            },
        ]);
    }
}
