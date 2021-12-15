<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\IO;

final class StreamTest extends TestCase
{
    public function testReadHandle(): void
    {
        $file = File\open_read_only(__FILE__);
        $stream = new IO\ReadStreamHandle($file->getStream());

        static::assertSame('<?php', $stream->readAll(5));

        $file->close();
    }

    public function testCloseReadHandle(): void
    {
        $file = File\open_read_only(__FILE__);
        $stream = new IO\CloseReadStreamHandle($file->getStream());

        static::assertSame('<?php', $stream->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testCloseWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new IO\CloseWriteStreamHandle($file->getStream());
        $stream->writeAll('<?php');

        $file->seek(0);
        static::assertSame('<?php', $file->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryWrite('Hello');
    }

    public function testCloseReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new IO\CloseReadWriteStreamHandle($file->getStream());
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
        $stream = new IO\ReadWriteStreamHandle($file->getStream());
        $stream->writeAll('<?php');

        $file->seek(0);

        static::assertSame('<?php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new IO\SeekReadWriteStreamHandle($file->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testCloseSeekReadWriteHandle(): void
    {
        $file = File\temporary();
        $stream = new IO\CloseSeekReadWriteStreamHandle($file->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekReadHandle(): void
    {
        $file = File\temporary();
        $file->writeAll('<?php');

        $stream = new IO\SeekReadStreamHandle($file->getStream());
        $stream->seek(2);
        static::assertSame('php', $stream->readAll());

        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekWriteHandle(): void
    {
        $file = File\temporary();
        $file = File\open_write_only($file->getPath());

        $stream = new IO\SeekWriteStreamHandle($file->getStream());
        $stream->seek(2);
        $stream->writeAll('<?php');

        $file->seek(0);
        $file->close();

        $file = File\open_read_only($file->getPath());
        static::assertSame("\0\0<?php", $file->readAll());
        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryWrite('hello');
    }
}
