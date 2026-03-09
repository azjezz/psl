<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\TCP;

final class CopyBidirectionalTest extends TestCase
{
    public function testBidirectionalCopy(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'proxy' => static function () use ($listener): void {
                $a = $listener->accept();
                $b = $listener->accept();

                [$a_to_b, $b_to_a] = IO\copy_bidirectional($a, $b);

                // a sent "hello" (5 bytes), b sent "world!" (6 bytes)
                static::assertSame(5, $a_to_b);
                static::assertSame(6, $b_to_a);

                $a->close();
                $b->close();
                $listener->close();
            },
            'side_a' => static function () use ($port): void {
                $conn = TCP\connect('127.0.0.1', $port);
                $conn->writeAll('hello');
                $conn->shutdown();
                // Read what b sent via the proxy
                $data = $conn->readAll();
                static::assertSame('world!', $data);
                $conn->close();
            },
            'side_b' => static function () use ($port): void {
                // Small delay to ensure a connects first (deterministic accept order)
                Async\sleep(Duration::milliseconds(5));
                $conn = TCP\connect('127.0.0.1', $port);
                $conn->writeAll('world!');
                $conn->shutdown();
                // Read what a sent via the proxy
                $data = $conn->readAll();
                static::assertSame('hello', $data);
                $conn->close();
            },
        ]);
    }
}
