<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\IO;

final class PipeTest extends TestCase
{
    public function testReadWrite(): void
    {
        [$read, $write] = IO\pipe();

        static::assertSame('', $read->readImmediately());
        $write->writeAll('hello');
        static::assertSame('hello', $read->read());

        static::assertSame('', $read->readImmediately());
        $write->writeAll('hello');
        static::assertSame('hello', $read->read());

        $write->writeAll('hello, world!');
        $write->close();

        static::assertSame('hello, world!', $read->readAll());

        $read->close();
    }

    public function testReadWriteConcurrently(): void
    {
        [$read, $write] = IO\pipe();

        $spy = new Psl\Ref('');

        $read_result = Async\run(static function () use ($read, $spy): string {
            $spy->value .= '[read:sleep]';
            Async\sleep(0.003);
            $spy->value .= '[read:start]';
            $content = $read->readAll(1000);
            $spy->value .= '[read:complete]';
            $read->close();
            $spy->value .= '[read:close]';
            return $content;
        });

        Async\run(static function () use ($write, $spy): void {
            $spy->value .= '[write:sleep]';
            Async\sleep(0.0035);
            $spy->value .= '[write:start]';
            $write->writeAll('hello');
            $spy->value .= '[write:complete]';
            $write->close();
            $spy->value .= '[write:close]';
        })->await();

        $result = $read_result->await();

        static::assertSame('hello', $result);
        static::assertSame('[read:sleep][write:sleep][read:start][write:start][write:complete][write:close][read:complete][read:close]', $spy->value);
    }

    public function testReadFixedSize(): void
    {
        [$read, $write] = IO\pipe();

        $write->writeAll('');
        $write->writeAll('hello!');
        $write->close();

        static::assertSame('he', $read->readFixedSize(2));
    }

    public function testReadFixedSizeFromClosedPipe(): void
    {
        [$read, $write] = IO\pipe();

        $write->writeAll('');
        $write->writeAll('hello!');
        $write->close();

        $this->expectException(IO\Exception\RuntimeException::class);
        $this->expectExceptionMessage('8 bytes were requested, but only able to read 6 bytes');

        $read->readFixedSize(8);
    }

    public function testReadAllTimedOut(): void
    {
        [$read, $_write] = IO\pipe();

        $this->expectException(IO\Exception\TimeoutException::class);
        $this->expectExceptionMessage('reached timeout while the handle is still not readable.');

        $read->readAll(timeout: 0.001);
    }

    public function testReadOnAlreadyClosedPipe(): void
    {
        [$read, $_write] = IO\pipe();

        Async\Scheduler::defer(static fn() => $read->close());
        $b = Async\run(static fn() => $read->readAll());

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        // > $b starts
        // > while waiting for $read to become readable, switch to defer
        // > defer calls close()
        // > close() throws in read suspension
        // > $b fails with the already closed exception.

        $b->await();
    }

    public function testWriteOnAlreadyClosedPipe(): void
    {
        [$_read, $write] = IO\pipe();

        Async\Scheduler::defer(static fn() => $write->close());

        $b = Async\run(static fn() => $write->writeAll('hello'));

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        // > $b starts
        // > while waiting for $write to become writable, switch to defer
        // > defer calls close()
        // > close() throws in read suspension
        // > $b fails with the already closed exception.

        $b->await();
    }
}
