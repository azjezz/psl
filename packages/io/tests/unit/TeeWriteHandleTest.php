<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\IO\Tests\Fixture\NonCloseableWriteHandle;
use Psl\IO\Tests\Fixture\SlowWriteHandle;

final class TeeWriteHandleTest extends TestCase
{
    public function testBasicWriteGoesToBothHandles(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->writeAll('hello world');

        static::assertSame('hello world', $first->getBuffer());
        static::assertSame('hello world', $second->getBuffer());
    }

    public function testTryWriteGoesToBothHandles(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $written = $tee->tryWrite('foobar');

        static::assertSame(6, $written);
        static::assertSame('foobar', $first->getBuffer());
        static::assertSame('foobar', $second->getBuffer());
    }

    public function testTryWriteWhenSecondIsSlower(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(3);
        $tee = new IO\TeeWriteHandle($first, $second);

        $written = $tee->tryWrite('abcdef');

        static::assertSame(6, $written);
        static::assertSame('abcdef', $first->getBuffer());
        static::assertSame('abc', $second->getWrittenData());
    }

    public function testBackpressureWhenPendingBufferExists(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(3);
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->tryWrite('abcdef');

        $written = $tee->tryWrite('ghijkl');
        static::assertSame(0, $written);
    }

    public function testBackpressureDrainsAndAcceptsNewData(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(3);
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->tryWrite('abcdef');
        static::assertSame('abc', $second->getWrittenData());

        static::assertSame(0, $tee->tryWrite('x'));

        $second->setMaxBytesPerWrite(3);
        $written = $tee->tryWrite('ghij');
        static::assertSame(4, $written);
        static::assertSame('abcdefghij', $first->getBuffer());
        static::assertSame('abcdef', $second->getWrittenData());

        $second->setMaxBytesPerWrite(100);
        $written = $tee->tryWrite('kl');
        static::assertSame(2, $written);
        static::assertSame('abcdefghijkl', $first->getBuffer());
        static::assertSame('abcdefghijkl', $second->getWrittenData());
    }

    public function testWriteBlocksOnDrainingPendingToSecond(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(3);
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->tryWrite('abcdef');

        $second->setMaxBytesPerWrite(100);

        $written = $tee->write('ghij');

        static::assertSame(4, $written);
        static::assertSame('abcdefghij', $first->getBuffer());
        static::assertSame('abcdefghij', $second->getWrittenData());
    }

    public function testWriteAllEnsuresBothHandlesGetAllData(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->writeAll('hello');
        $tee->writeAll(' ');
        $tee->writeAll('world');

        static::assertSame('hello world', $first->getBuffer());
        static::assertSame('hello world', $second->getBuffer());
    }

    public function testCloseClosesBothHandlesAndClearsPending(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(3);
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->tryWrite('abcdef');

        $tee->close();

        static::assertTrue($tee->isClosed());
        static::assertTrue($first->isClosed());
        static::assertTrue($second->isClosed());
    }

    public function testCloseClosesNonCloseableHandlesGracefully(): void
    {
        $first = new NonCloseableWriteHandle();
        $second = new NonCloseableWriteHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->close();

        static::assertTrue($tee->isClosed());
    }

    /**
     * @param (Closure(IO\TeeWriteHandle): mixed) $operation
     */
    #[DataProvider('provideOperationsAfterClose')]
    public function testOperationsThrowAfterClose(Closure $operation): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($tee);
    }

    /**
     * @return iterable<string, array{(Closure(IO\TeeWriteHandle): mixed)}>
     */
    public static function provideOperationsAfterClose(): iterable
    {
        yield 'tryWrite' => [static fn(IO\TeeWriteHandle $h) => $h->tryWrite('data')];
        yield 'write' => [static fn(IO\TeeWriteHandle $h) => $h->write('data')];
        yield 'writeAll' => [static fn(IO\TeeWriteHandle $h) => $h->writeAll('data')];
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        static::assertFalse($tee->isClosed());
    }

    public function testDataIdenticalAfterMultipleOperations(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->writeAll('Hello, ');
        $tee->writeAll('World!');
        $tee->write("\n");
        $tee->writeAll('Line two');

        static::assertSame($first->getBuffer(), $second->getBuffer());
        static::assertSame("Hello, World!\nLine two", $first->getBuffer());
    }

    public function testTryWriteReturnsZeroWhenFirstReturnsZero(): void
    {
        $first = new SlowWriteHandle(0);
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $written = $tee->tryWrite('data');

        static::assertSame(0, $written);
        static::assertSame('', $second->getBuffer());
    }

    public function testTryWriteEmptyStringReturnsZero(): void
    {
        $first = new IO\MemoryHandle();
        $second = new IO\MemoryHandle();
        $tee = new IO\TeeWriteHandle($first, $second);

        $written = $tee->tryWrite('');

        static::assertSame(0, $written);
    }

    public function testPendingBufferDrainsIncrementally(): void
    {
        $first = new IO\MemoryHandle();
        $second = new SlowWriteHandle(1);
        $tee = new IO\TeeWriteHandle($first, $second);

        $tee->tryWrite('abcde');
        static::assertSame('abcde', $first->getBuffer());
        static::assertSame('a', $second->getWrittenData());

        $second->setMaxBytesPerWrite(1);
        static::assertSame(0, $tee->tryWrite('f'));
        static::assertSame('ab', $second->getWrittenData());

        $second->setMaxBytesPerWrite(1);
        static::assertSame(0, $tee->tryWrite('f'));
        static::assertSame('abc', $second->getWrittenData());

        $second->setMaxBytesPerWrite(1);
        static::assertSame(0, $tee->tryWrite('f'));
        static::assertSame('abcd', $second->getWrittenData());

        $second->setMaxBytesPerWrite(100);
        $written = $tee->tryWrite('f');
        static::assertSame(1, $written);
        static::assertSame('abcdef', $first->getBuffer());
        static::assertSame('abcdef', $second->getWrittenData());
    }
}
