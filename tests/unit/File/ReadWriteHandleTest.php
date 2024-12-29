<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\IO;
use Psl\OS;

final class ReadWriteHandleTest extends TestCase
{
    public function testGetPath(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $path = $handle->getPath();
        $handle->close();

        $handle = new File\ReadWriteHandle($file);

        static::assertSame($path, $handle->getPath());
    }

    public function testGetSize(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $path = $handle->getPath();
        $handle->close();

        $handle = new File\ReadWriteHandle($path);

        static::assertSame(0, $handle->getSize());

        $handle->writeAll('hello');
        $handle->seek(3);

        static::assertSame(3, $handle->tell());
        static::assertSame(5, $handle->getSize());
        static::assertSame(3, $handle->tell());
    }

    public function testReading(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->writeAll('herpderp');
        $handle->seek(0);
        static::assertSame('herp', $handle->readFixedSize(4));
        static::assertSame('derp', $handle->read());
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
        static::assertSame(8, $handle->tell());
        $handle->seek(0);
        static::assertSame(0, $handle->tell());
        static::assertSame('herpderp', $handle->read());
        $handle->seek(4);
        static::assertSame(4, $handle->tell());
        static::assertSame('derp', $handle->read());
    }

    public function testGetStream(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->writeAll('herpderp');

        $file_stream = $handle->getStream();
        static::assertIsNotClosedResource($file_stream);

        $handle->close();

        static::assertIsClosedResource($file_stream);

        static::assertNull($handle->getStream());
    }

    public function testMustCreateExistingFile(): void
    {
        $this->expectException(File\Exception\AlreadyCreatedException::class);
        $this->expectExceptionMessage('is already created.');

        new File\ReadWriteHandle(__FILE__, File\WriteMode::MustCreate);
    }

    public function testAppendToNonExistingFile(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = File\open_read_write($temporary_file, File\WriteMode::Append);
        $handle->writeAll('hello');
        $handle->seek(0);

        $content = $handle->readAll();

        static::assertSame('hello', $content);

        $handle->close();

        static::assertTrue(Filesystem\is_file($temporary_file));
    }

    public function testAppendToANonWritableFile(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporary_file, 0o555);

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('File "' . $temporary_file . '" is not writable.');

        new File\ReadWriteHandle($temporary_file, File\WriteMode::Append);
    }

    public function testOpenNonReadableFile(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporary_file, 0o333);

        $this->expectException(File\Exception\NotReadableException::class);
        $this->expectExceptionMessage('File "' . $temporary_file . '" is not readable.');

        new File\ReadWriteHandle($temporary_file, File\WriteMode::Append);
    }

    public function testThrowsWhenCreatingFile(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);
        Filesystem\create_directory($temporary_file);
        Filesystem\change_permissions($temporary_file, 0o555);

        $file = $temporary_file . Filesystem\SEPARATOR . 'foo';

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('File "' . $file . '" is not writable.');

        new File\ReadWriteHandle($file, File\WriteMode::MustCreate);
    }

    public function testOpenDirectory(): void
    {
        $this->expectException(File\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . Env\temp_dir() . '" does not point to a file.');

        new File\ReadWriteHandle(Env\temp_dir(), File\WriteMode::Append);
    }

    public function testCreateNonExisting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = File\open_read_write($temporary_file, File\WriteMode::MustCreate);
        $handle->tryWrite('hello');
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
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
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
            static fn(File\ReadHandleInterface $handle) => $handle->tryRead(),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->lock(File\LockType::Exclusive),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->tryLock(File\LockType::Exclusive),
        ];

        yield [
            static fn(File\HandleInterface $handle) => $handle->getSize(),
        ];
    }
}
