<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\TCP;

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

    public function testCopyTimeout(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        $this->expectException(IO\Exception\TimeoutException::class);

        try {
            Async\concurrently([
                'server' => static function () use ($listener): void {
                    $conn = $listener->accept();
                    // Write some data, then hold the connection open without closing
                    $conn->writeAll('partial');
                    // Sleep longer than the timeout
                    Async\sleep(Duration::seconds(5));
                    $conn->close();
                    $listener->close();
                },
                'client' => static function () use ($port): void {
                    $client = TCP\connect('127.0.0.1', $port);
                    $destination = new IO\MemoryHandle();

                    // Copy with a short timeout — server won't close in time
                    IO\copy($client, $destination, Duration::milliseconds(100));
                },
            ]);
        } finally {
            $listener->close();
        }
    }

    public function testCopyRetriesOnEmptyNonEofRead(): void
    {
        $state = new \Psl\Ref(0);
        $reader = new class($state) implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            private bool $eof = false;

            /**
             * @param \Psl\Ref<int> $state
             */
            public function __construct(
                private \Psl\Ref $state,
            ) {}

            public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
            {
                $this->state->value++;
                if ($this->state->value === 1) {
                    // First call: return empty string without setting EOF
                    return '';
                }

                if ($this->state->value === 2) {
                    // Second call: return actual data
                    return 'data';
                }

                // Third call: signal EOF
                $this->eof = true;
                return '';
            }

            public function tryRead(null|int $max_bytes = null): string
            {
                return $this->read($max_bytes);
            }

            public function reachedEndOfDataSource(): bool
            {
                return $this->eof;
            }
        };

        $destination = new IO\MemoryHandle();
        $bytes = IO\copy($reader, $destination);

        static::assertSame(4, $bytes);
        // Verify that read was called 3 times: empty (continue), data, eof
        static::assertSame(3, $state->value);
        $destination->seek(0);
        static::assertSame('data', $destination->readAll());

        $destination->close();
    }
}
