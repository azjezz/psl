<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IO;

final class StreamTest extends TestCase
{
    public function testReadHandle(): void
    {
        $resource = fopen(__FILE__, 'r');
        static::assertIsResource($resource);
        $stream = new IO\ReadStreamHandle($resource);

        static::assertSame('<?php', $stream->readAll(5));

        fclose($resource);
    }

    public function testCloseReadHandle(): void
    {
        $resource = fopen(__FILE__, 'r');
        static::assertIsResource($resource);
        $stream = new IO\CloseReadStreamHandle($resource);

        static::assertSame('<?php', $stream->readAll(5));

        $stream->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);

        $stream->tryRead();
    }

    public function testCloseWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'r+');
            static::assertIsResource($resource);
            $stream = new IO\CloseWriteStreamHandle($resource);
            $stream->writeAll('<?php');

            // Re-open for reading to verify
            $readResource = fopen($file, 'r');
            static::assertIsResource($readResource);
            $readHandle = new IO\ReadStreamHandle($readResource);
            static::assertSame('<?php', $readHandle->readAll(5));
            fclose($readResource);

            $stream->close();

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryWrite('Hello');
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testCloseReadWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'r+');
            static::assertIsResource($resource);
            $stream = new IO\CloseReadWriteStreamHandle($resource);
            $stream->writeAll('<?php');

            fseek($resource, 0);

            static::assertSame('<?php', $stream->readAll());

            $stream->close();

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->readAll();
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testReadWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'r+');
            static::assertIsResource($resource);
            $stream = new IO\ReadWriteStreamHandle($resource);
            $stream->writeAll('<?php');

            fseek($resource, 0);

            static::assertSame('<?php', $stream->readAll());

            fclose($resource);

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryRead();
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testSeekReadWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'r+');
            static::assertIsResource($resource);
            $stream = new IO\SeekReadWriteStreamHandle($resource);
            $stream->writeAll('<?php');
            $stream->seek(0);
            static::assertSame('<?php', $stream->readAll());

            fclose($resource);

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryRead();
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testCloseSeekReadWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'r+');
            static::assertIsResource($resource);
            $stream = new IO\CloseSeekReadWriteStreamHandle($resource);
            $stream->writeAll('<?php');
            $stream->seek(0);
            static::assertSame('<?php', $stream->readAll());

            $stream->close();

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryRead();
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testSeekReadHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            // Write content first
            file_put_contents($file, '<?php');

            $resource = fopen($file, 'r');
            static::assertIsResource($resource);
            $stream = new IO\SeekReadStreamHandle($resource);
            $stream->seek(2);
            static::assertSame('php', $stream->readAll());

            fclose($resource);

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryRead();
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testSeekWriteHandle(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'psl');
        static::assertNotFalse($file);

        try {
            $resource = fopen($file, 'w');
            static::assertIsResource($resource);

            $stream = new IO\SeekWriteStreamHandle($resource);
            $stream->seek(2);
            $stream->writeAll('<?php');

            fclose($resource);

            static::assertSame("\0\0<?php", file_get_contents($file));

            $this->expectException(IO\Exception\AlreadyClosedException::class);

            $stream->tryWrite('hello');
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
