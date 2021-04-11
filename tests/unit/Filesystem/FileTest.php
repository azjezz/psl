<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Env;
use Psl\Exception\InvariantViolationException;
use Psl\Filesystem;
use Psl\Str;

use function time;

final class FileTest extends AbstractFilesystemTest
{
    protected string $function = 'file';

    public function testTemporaryFile(): void
    {
        $file = Filesystem\create_temporary_file($this->directory);

        static::assertTrue(Filesystem\is_file($file));
        static::assertSame($this->directory, Filesystem\get_directory($file));
    }

    public function testTemporaryFileWithPrefix(): void
    {
        $file = Filesystem\create_temporary_file($this->directory, 'foo');

        static::assertTrue(Filesystem\is_file($file));
        static::assertSame($this->directory, Filesystem\get_directory($file));
        static::assertStringContainsString('foo', Filesystem\get_filename($file));
    }

    public function testTemporaryFileIsCreateInTempDirectoryByDefault(): void
    {
        $file = Filesystem\create_temporary_file();

        static::assertSame(Env\temp_dir(), Filesystem\get_directory($file));
    }

    public function testTemporaryFileThrowsForNonExistingDirectory(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('$directory is not a directory.');

        Filesystem\create_temporary_file(__FILE__);
    }

    public function testTemporaryFileThrowsForPrefixWithSeparator(): void
    {
        $prefix = Str\join(['a', 'b'], Filesystem\SEPARATOR);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage(
            '$prefix should not contain a directory separator ( "' . Filesystem\SEPARATOR . '" ).'
        );

        Filesystem\create_temporary_file($this->directory, $prefix);
    }

    public function testCreateFileAndParentDirectory(): void
    {
        $directory = Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR);
        $file = Str\join([$directory, 'write.txt'], Filesystem\SEPARATOR);

        static::assertFalse(Filesystem\is_directory($directory));

        Filesystem\create_file($file);

        static::assertTrue(Filesystem\is_directory($directory));
        static::assertTrue(Filesystem\is_file($file));
    }

    public function testWriteFile(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        static::assertFileDoesNotExist($file);

        Filesystem\write_file($file, 'Hello!');

        static::assertFileExists($file);

        static::assertStringEqualsFile($file, 'Hello!');

        Filesystem\write_file($file, 'Hello');

        static::assertStringEqualsFile($file, 'Hello');

        Filesystem\append_file($file, ', World!');

        static::assertStringEqualsFile($file, 'Hello, World!');

        Filesystem\delete_file($file);
    }

    public function testWriteFileThrowsForDirectories(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('$file is not a file.');

        Filesystem\write_file($this->directory, 'hello');
    }

    public function testWriteFileThrowsForNonWritableFiles(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        Filesystem\create_file($file);
        Filesystem\change_permissions($file, 0111);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('$file is not writeable.');

        Filesystem\write_file($file, 'hello');
    }

    public function testReadFile(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        Filesystem\write_file($file, 'PHP Standard Library');
        Filesystem\append_file($file, ' - a modern, consistent, centralized');
        Filesystem\append_file($file, ' well-typed set of APIs for PHP programmers.');

        $content = Filesystem\read_file($file, 0, 20);

        static::assertSame('PHP Standard Library', $content);

        $content = Filesystem\read_file($file, 84, 16);

        static::assertSame('PHP programmers.', $content);
    }

    public function testFileModificationAndAccessTime(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        $modification_time = time() - 3600;
        $access_time = time() - 1800;

        Filesystem\create_file($file, $modification_time, $access_time);

        static::assertSame($modification_time, Filesystem\get_modification_time($file));
        static::assertSame($access_time, Filesystem\get_access_time($file));
    }

    public function testFileAccessTime(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        $access_time = time() - 1800;

        Filesystem\create_file($file, null, $access_time);

        static::assertSame($access_time, Filesystem\get_modification_time($file));
        static::assertSame($access_time, Filesystem\get_access_time($file));
    }

    public function testFileModificationTime(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        $modification_time = time() - 3600;

        Filesystem\create_file($file, $modification_time);

        static::assertSame($modification_time, Filesystem\get_modification_time($file));
        static::assertSame($modification_time, Filesystem\get_access_time($file));
    }

    public function testFileChangeTime(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        static::assertEqualsWithDelta(time(), Filesystem\get_change_time($file), 1.0);
    }
}
