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
            Async\usleep(3000);
            $spy->value .= '[read:start]';
            $content = $read->readAll(1000);
            $spy->value .= '[read:complete]';
            $read->close();
            $spy->value .= '[read:close]';
            return $content;
        });

        Async\run(static function () use ($write, $spy): void {
            $spy->value .= '[write:sleep]';
            Async\usleep(3500);
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

        $read->readAll(timeout_ms: 100);
    }
}
