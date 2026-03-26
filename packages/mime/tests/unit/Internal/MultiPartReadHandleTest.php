<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\MIME\Headers;
use Psl\MIME\Internal\MultiPartReadHandle;
use Psl\MIME\MultiPart\Alternative;
use Psl\MIME\MultiPart\Composite;
use Psl\MIME\Part\Part;

final class MultiPartReadHandleTest extends TestCase
{
    public function testTryReadEmptyParts(): void
    {
        $handle = new MultiPartReadHandle('boundary', []);
        $result = $handle->tryRead();

        static::assertSame("\r\n--boundary--\r\n", $result);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadSinglePart(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('hello'));

        $handle = new MultiPartReadHandle('boundary', [$part]);
        $output = new IO\MemoryHandle();
        IO\copy($handle, $output);

        $content = $output->getBuffer();

        static::assertStringContainsString('--boundary', $content);
        static::assertStringContainsString('Content-Type: text/plain', $content);
        static::assertStringContainsString('hello', $content);
        static::assertStringContainsString('--boundary--', $content);
    }

    public function testTryReadMultipleParts(): void
    {
        $part1 = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('first'));
        $part2 = new Part(Headers::fromPairs([['Content-Type', 'text/html']]), new IO\MemoryHandle('second'));

        $handle = new MultiPartReadHandle('boundary', [$part1, $part2]);
        $output = new IO\MemoryHandle();
        IO\copy($handle, $output);

        $content = $output->getBuffer();

        static::assertStringContainsString('first', $content);
        static::assertStringContainsString('second', $content);
        static::assertStringContainsString('text/plain', $content);
        static::assertStringContainsString('text/html', $content);
    }

    public function testTryReadWithMaxBytesFirstChunk(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('hello world'));

        $handle = new MultiPartReadHandle('boundary', [$part]);

        $first = $handle->tryRead(5);
        static::assertSame('--bou', $first);

        $second = $handle->tryRead(5);
        static::assertSame('ndary', $second);
    }

    public function testReadWithMaxBytesFirstChunk(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('hello world'));

        $handle = new MultiPartReadHandle('boundary', [$part]);

        $first = $handle->read(5);
        static::assertSame('--bou', $first);

        $second = $handle->read(5);
        static::assertSame('ndary', $second);
    }

    public function testReadReturnsEmptyAtEof(): void
    {
        $handle = new MultiPartReadHandle('boundary', []);
        $handle->tryRead();

        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadReturnsEmptyAtEof(): void
    {
        $handle = new MultiPartReadHandle('boundary', []);
        $handle->tryRead();

        static::assertSame('', $handle->tryRead());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadDrainsBufferFirst(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('x'));

        $handle = new MultiPartReadHandle('boundary', [$part]);

        $first = $handle->read(2);
        static::assertSame('--', $first);

        $second = $handle->read(2);
        static::assertNotEmpty($second);
    }

    public function testDrainBufferReturnsPartialChunk(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('test data here'));

        $handle = new MultiPartReadHandle('boundary', [$part]);

        $firstByte = $handle->tryRead(1);
        static::assertSame('-', $firstByte);

        $secondByte = $handle->tryRead(1);
        static::assertSame('-', $secondByte);
    }

    public function testCompositeBodyTryRead(): void
    {
        $body = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('main body'));

        $composite = new Composite($body, 'composite-boundary');
        $composite->addPart(
            new Part(Headers::fromPairs([['Content-Type', 'application/pdf']]), new IO\MemoryHandle('pdf-data')),
        );

        $output = new IO\MemoryHandle();
        IO\copy($composite->body(), $output);

        $content = $output->getBuffer();

        static::assertStringContainsString('main body', $content);
        static::assertStringContainsString('pdf-data', $content);
        static::assertStringContainsString('--composite-boundary--', $content);
    }

    public function testAlternativeBodyTryRead(): void
    {
        $alternative = new Alternative('alt-boundary');
        $alternative->addPart(
            new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('plain text')),
        );
        $alternative->addPart(
            new Part(Headers::fromPairs([['Content-Type', 'text/html']]), new IO\MemoryHandle('<b>html</b>')),
        );

        $output = new IO\MemoryHandle();
        IO\copy($alternative->body(), $output);

        $content = $output->getBuffer();

        static::assertStringContainsString('plain text', $content);
        static::assertStringContainsString('<b>html</b>', $content);
        static::assertStringContainsString('--alt-boundary--', $content);
    }

    public function testTryReadSmallMaxBytesDrainsPartially(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('AB'));

        $handle = new MultiPartReadHandle('b', [$part]);

        $c1 = $handle->tryRead(1);
        static::assertSame('-', $c1);
        $c2 = $handle->tryRead(1);
        static::assertSame('-', $c2);
        $c3 = $handle->tryRead(1);
        static::assertSame('b', $c3);
    }

    public function testReadSmallMaxBytesDrainsPartially(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('AB'));

        $handle = new MultiPartReadHandle('b', [$part]);

        $c1 = $handle->read(1);
        static::assertSame('-', $c1);
        $c2 = $handle->read(1);
        static::assertSame('-', $c2);
        $c3 = $handle->read(1);
        static::assertSame('b', $c3);
    }

    public function testEmptyPartsClosingBoundary(): void
    {
        $handle = new MultiPartReadHandle('empty-boundary', []);

        static::assertFalse($handle->reachedEndOfDataSource());

        $content = $handle->read();

        static::assertSame("\r\n--empty-boundary--\r\n", $content);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceInitiallyFalse(): void
    {
        $handle = new MultiPartReadHandle('b', []);

        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceAfterFullRead(): void
    {
        $part = new Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('data'));

        $handle = new MultiPartReadHandle('b', [$part]);
        $output = new IO\MemoryHandle();
        IO\copy($handle, $output);

        static::assertTrue($handle->reachedEndOfDataSource());
    }
}
