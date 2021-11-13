<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\File;
use Psl\Filesystem;
use Psl\IO;

final class ReadWriteHandleTest extends TestCase
{
    public function testGetPath(): void
    {
        $file = File\temporary();
        $path = $file->getPath();
        $file->close();

        $file = new File\ReadWriteHandle($path);

        static::assertSame($path, $file->getPath());
    }

    public function testGetSize(): void
    {
        $file = File\temporary();
        $path = $file->getPath();
        $file->close();

        $file = new File\ReadWriteHandle($path);

        static::assertSame(0, $file->getSize());

        $file->writeAll('hello');
        $file->seek(3);

        static::assertSame(3, $file->tell());
        static::assertSame(5, $file->getSize());
        static::assertSame(3, $file->tell());
    }

    public function testReading(): void
    {
        $file = File\temporary();
        $file->writeAll('herpderp');
        $file->seek(0);
        static::assertSame('herp', $file->readFixedSize(4));
        static::assertSame('derp', $file->read());
        static::assertSame('', $file->read());
        static::assertSame('', $file->read());
        static::assertSame(8, $file->tell());
        $file->seek(0);
        static::assertSame(0, $file->tell());
        static::assertSame('herpderp', $file->read());
        $file->seek(4);
        static::assertSame(4, $file->tell());
        static::assertSame('derp', $file->read());
    }

    public function testGetStream(): void
    {
        $file = File\temporary();
        $file->writeAll('herpderp');

        $file_stream = $file->getStream();
        static::assertIsNotClosedResource($file_stream);

        $file->close();

        static::assertIsClosedResource($file_stream);

        static::assertNull($file->getStream());
    }

    public function testMustCreateExistingFile(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('already exists.');

        new File\ReadWriteHandle(__FILE__, File\WriteMode::MUST_CREATE);
    }

    public function testAppendToNonExistingFile(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('does not exist.');

        new File\ReadWriteHandle(__FILE__ . '.fake', File\WriteMode::APPEND);
    }

    public function testAppendToANonWritableFile(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporary_file, 0555);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('is not writable.');

        new File\ReadWriteHandle($temporary_file, File\WriteMode::APPEND);
    }

    public function testCreateNonExisting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = File\open_read_write($temporary_file, File\WriteMode::MUST_CREATE);
        $handle->writeImmediately('hello');
        $handle->seek(0);

        $content = $handle->readAll();

        static::assertSame('hello', $content);

        $handle->close();

        static::assertTrue(Filesystem\is_file($temporary_file));
    }

    /**
     * @param (callable(File\ReadWriteHandleInterface): mixed) $operation
     *
     * @dataProvider provideOperations
     */
    public function testClose(callable $operation): void
    {
        $file = File\temporary();
        $file->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($file);
    }

    /**
     * @return iterable<(callable(File\ReadWriteHandleInterface): mixed)>
     */
    public function provideOperations(): iterable
    {
        yield [
            static fn(File\HandleInterface $handle) => $handle->seek(5),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->tell(),
        ];

        yield [
            static fn(File\WriteHandleInterface $handle) => $handle->write('hello'),
        ];

        yield [
            static fn(File\WriteHandleInterface $handle) => $handle->writeAll('hello'),
        ];

        yield [
            static fn(File\ReadHandleInterface $handle) => $handle->read(),
        ];

        yield [
            static fn(File\ReadHandleInterface $handle) => $handle->readAll(),
        ];

        yield [
            static fn(File\ReadHandleInterface $handle) => $handle->readImmediately(),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->lock(File\LockType::EXCLUSIVE),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->tryLock(File\LockType::EXCLUSIVE),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->getSize(),
        ];
    }
}
