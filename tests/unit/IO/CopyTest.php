<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\IO;

final class CopyTest extends TestCase
{
    public function testCopyFromReaderToWriter(): void
    {
        [$read, $write] = IO\pipe();
        $destination = new IO\MemoryHandle();

        $write->writeAll('hello, world!');
        $write->close();

        $bytes = IO\copy($read, $destination);

        static::assertSame(13, $bytes);
        $destination->seek(0);
        static::assertSame('hello, world!', $destination->readAll());

        $read->close();
        $destination->close();
    }

    public function testCopyEmptySource(): void
    {
        [$read, $write] = IO\pipe();
        $destination = new IO\MemoryHandle();

        $write->close();

        $bytes = IO\copy($read, $destination);

        static::assertSame(0, $bytes);

        $read->close();
        $destination->close();
    }

    public function testCopyLargeData(): void
    {
        [$read, $write] = IO\pipe();
        $destination = new IO\MemoryHandle();

        $data = str_repeat('x', 100_000);

        // Write and copy must run concurrently — the pipe buffer is limited,
        // so writeAll blocks until the reader drains it.
        Async\concurrently([
            'write' => static function () use ($write, $data): void {
                $write->writeAll($data);
                $write->close();
            },
            'copy' => static fn() => IO\copy($read, $destination),
        ]);

        $destination->seek(0);
        static::assertSame($data, $destination->readAll());

        $read->close();
        $destination->close();
    }
}
