<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;
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
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $stream = new IO\CloseWriteStreamHandle($handle->getStream());
        $stream->writeAll('<?php');

        $handle->seek(0);
        static::assertSame('<?php', $handle->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryWrite('Hello');
    }

    public function testCloseReadWriteHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $stream = new IO\CloseReadWriteStreamHandle($handle->getStream());
        $stream->writeAll('<?php');

        $handle->seek(0);

        static::assertSame('<?php', $stream->readAll());

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->readAll();
    }

    public function testReadWriteHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $stream = new IO\ReadWriteStreamHandle($handle->getStream());
        $stream->writeAll('<?php');

        $handle->seek(0);

        static::assertSame('<?php', $stream->readAll());

        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekReadWriteHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $stream = new IO\SeekReadWriteStreamHandle($handle->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testCloseSeekReadWriteHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $stream = new IO\CloseSeekReadWriteStreamHandle($handle->getStream());
        $stream->writeAll('<?php');
        $stream->seek(0);
        static::assertSame('<?php', $stream->readAll());

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekReadHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->writeAll('<?php');

        $stream = new IO\SeekReadStreamHandle($handle->getStream());
        $stream->seek(2);
        static::assertSame('php', $stream->readAll());

        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testSeekWriteHandle(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_write_only($file);

        $stream = new IO\SeekWriteStreamHandle($handle->getStream());
        $stream->seek(2);
        $stream->writeAll('<?php');

        $handle->seek(0);
        $handle->close();

        $handle = File\open_read_only($file);
        static::assertSame("\0\0<?php", $handle->readAll());
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryWrite('hello');
    }
}
