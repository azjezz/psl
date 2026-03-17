<?php

declare(strict_types=1);

namespace Psl\Encoding\Tests\Unit\Hex;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Hex;
use Psl\IO;

use function strlen;

final class EncodingReadHandleTest extends TestCase
{
    public function testTryReadBufferNonEmptyNotEofDoesNotRefill(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABCDEFGHIJ'));
        $first = $handle->tryRead(4);
        static::assertSame(4, strlen($first));
        $second = $handle->tryRead(4);
        static::assertSame(4, strlen($second));
    }

    public function testTryReadEmptyHandleReturnsEmptyString(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadMaxBytesExactlyEqualsBuffer(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABC'));
        $all = $handle->readAll();
        $len = strlen($all);

        $handle2 = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABC'));
        $result = $handle2->tryRead($len);
        static::assertSame($len, strlen($result));
        static::assertSame('', $handle2->tryRead());
    }

    public function testTryReadNullMaxBytesReturnsAll(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hello'));
        $result = $handle->tryRead(null);
        static::assertGreaterThan(0, strlen($result));
    }

    public function testTryReadMaxBytesLessThanBufferReturnsPartial(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hello, World!'));
        $part1 = $handle->tryRead(4);
        static::assertSame(4, strlen($part1));
        $rest = $handle->tryRead();
        static::assertGreaterThan(0, strlen($rest));
        $decoder = new Hex\DecodingReadHandle(new IO\MemoryHandle($part1 . $rest));
        static::assertSame('Hello, World!', $decoder->readAll());
    }

    public function testReadAfterEofWithEmptyBufferReturnsEmptyString(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('A'));
        $handle->readAll();
        static::assertSame('', $handle->read());
    }

    public function testReadAfterEofReturnsEmptyNotNull(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('A'));
        $handle->readAll();
        $result = $handle->read();
        static::assertIsString($result);
        static::assertSame('', $result);
    }

    public function testReadEmptyHandleReturnsEmptyString(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->read());
    }

    public function testReadMaxBytesExactlyEqualsBuffer(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('AB'));
        $all = $handle->readAll();
        $len = strlen($all);
        $handle2 = new Hex\EncodingReadHandle(new IO\MemoryHandle('AB'));
        static::assertSame($all, $handle2->read($len));
    }

    public function testReadMaxBytesLessThanBuffer(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABCDE'));
        $part = $handle->read(4);
        static::assertSame(4, strlen($part));
        $rest = $handle->readAll();
        static::assertGreaterThan(0, strlen($rest));
    }

    public function testReadMultipleSequentialReadsAfterEof(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('X'));
        $handle->readAll();
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
    }

    public function testReadByteSingleByteHandle(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('A'));
        $byte = $handle->readByte();
        static::assertSame(1, strlen($byte));
    }

    public function testReadByteSecondIsDifferent(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABCDE'));
        $b1 = $handle->readByte();
        $b2 = $handle->readByte();
        static::assertNotSame($b1, $b2);
    }

    public function testReadByteEmptyHandleThrows(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle(''));
        $this->expectException(IO\Exception\RuntimeException::class);
        $handle->readByte();
    }

    public function testReadByteMultiByteBufferThenReadByte(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABC'));
        $b1 = $handle->readByte();
        static::assertSame(1, strlen($b1));
        $rest = $handle->readAll();
        static::assertGreaterThan(0, strlen($rest));
    }

    public function testReadLineNoNewlineReturnsContentThenNull(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hello'));
        $line = $handle->readLine();
        static::assertNotNull($line);
        static::assertSame('48656c6c6f', $line);
        static::assertNull($handle->readLine());
    }

    public function testReadLineOnEmptyReturnsNull(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle(''));
        static::assertNull($handle->readLine());
    }

    public function testReadLineAfterPartialRead(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hello'));
        $handle->tryRead(2);
        $rest = $handle->readLine();
        static::assertNotNull($rest);
    }

    public function testReadUntilSuffixFound(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hi'));
        $result = $handle->readUntil('69');
        static::assertSame('48', $result);
    }

    public function testReadUntilSuffixNotFound(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABC'));
        static::assertNull($handle->readUntil('ZZ'));
    }

    public function testReadUntilMultipleSuffixes(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABA'));
        $result = $handle->readUntil('42');
        static::assertSame('41', $result);
        $rest = $handle->readAll();
        static::assertSame('41', $rest);
    }

    public function testReadUntilRemainingDataCorrect(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABC'));
        $before = $handle->readUntil('42');
        static::assertNotNull($before);
        $rest = $handle->readAll();
        static::assertGreaterThan(0, strlen($rest));
    }

    public function testReadUntilBoundedSuffixFoundWithinLimit(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hi'));
        $result = $handle->readUntilBounded('69', 10);
        static::assertSame('48', $result);
    }

    public function testReadUntilBoundedSuffixFoundBeyondLimitThrows(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('ABCDEFGHIJ'));
        $this->expectException(IO\Exception\OverflowException::class);
        $handle->readUntilBounded('ZZ', 3);
    }

    public function testReadUntilBoundedNotFoundWithinLimit(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('AB'));
        static::assertNull($handle->readUntilBounded('ZZ', 100));
    }

    public function testReadUntilBoundedExactLimit(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hi'));
        $result = $handle->readUntilBounded('69', 2);
        static::assertSame('48', $result);
    }

    public function testReadUntilBoundedOneOverThrows(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hi'));
        $this->expectException(IO\Exception\OverflowException::class);
        $handle->readUntilBounded('69', 1);
    }

    public function testReachedEndOfDataSourceStates(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('A'));
        static::assertFalse($handle->reachedEndOfDataSource());
        $handle->readAll();
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testFillBufferEncodedOutputCorrect(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('Hello'));
        $encoded = $handle->readAll();
        static::assertSame('48656c6c6f', $encoded);
    }

    public function testFillBufferEofHandling(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testFillBufferRoundTrip(): void
    {
        $data = str_repeat('B', 200);
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle($data));
        $encoded = $handle->readAll();
        $decoder = new Hex\DecodingReadHandle(new IO\MemoryHandle($encoded));
        static::assertSame($data, $decoder->readAll());
    }

    public function testFillBufferBinaryData(): void
    {
        $data = "\x00\x01\xff\xfe";
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle($data));
        $encoded = $handle->readAll();
        static::assertSame('0001fffe', $encoded);
    }

    public function testInterleavedMethodCalls(): void
    {
        $handle = new Hex\EncodingReadHandle(new IO\MemoryHandle('AB'));
        $b1 = $handle->readByte();
        static::assertSame('4', $b1);
        $b2 = $handle->readByte();
        static::assertSame('1', $b2);
        $rest = $handle->readAll();
        static::assertSame('42', $rest);
    }
}
