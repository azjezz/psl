<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\IO;
use Psl\IO\Stream;

final class StreamTest extends TestCase
{
    public function testReadHandle(): void
    {
        $file = File\open_read_only(__FILE__);
        $stream = new Stream\ReadHandle($file->getStream());

        static::assertSame('<?php', $stream->readAll(5));

        $file->close();
    }

    public function testCloseReadHandle(): void
    {
        $file = File\open_read_only(__FILE__);
        $stream = new Stream\CloseReadHandle($file->getStream());

        static::assertSame('<?php', $stream->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readImmediately();
    }

    public function testCloseWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new Stream\CloseWriteHandle($file->getStream());
        $stream->writeAll('<?php');

        $file->seek(0);
        static::assertSame('<?php', $file->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->writeImmediately('Hello');
    }

    public function testCloseReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new Stream\CloseReadWriteHandle($file->getStream());
        $stream->writeAll('<?php');

        $file->seek(0);

        static::assertSame('<?php', $stream->readAll());

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readAll();
    }

    public function testReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new Stream\ReadWriteHandle($file->getStream());
        $stream->writeAll('<?php');

        $file->seek(0);

        static::assertSame('<?php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readImmediately();
    }

    public function testSeekReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new Stream\SeekReadWriteHandle($file->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readImmediately();
    }

    public function testCloseSeekReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new Stream\CloseSeekReadWriteHandle($file->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readImmediately();
    }

    public function testSeekReadHandle(): void
    {
        $file = File\temporary();
        $file->writeAll('<?php');

        $stream = new Stream\SeekReadHandle($file->getStream());
        $stream->seek(2);
        static::assertSame('php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readImmediately();
    }

    public function testSeekWriteHandle(): void
    {
        $file = File\temporary();
        $file = File\open_write_only($file->getPath());

        $stream = new Stream\SeekWriteHandle($file->getStream());
        $stream->seek(2);
        $stream->writeAll('<?php');

        $file->seek(0);
        $file->close();

        $file = File\open_read_only($file->getPath());
        static::assertSame("\0\0<?php", $file->readAll());
        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->writeImmediately('hello');
    }
}
