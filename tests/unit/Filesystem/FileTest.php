<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Env;
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

    public function testTemporaryFileThrowsForNonDirectoryNode(): void
    {
        $this->expectException(Filesystem\Exception\NotDirectoryException::class);
        $this->expectExceptionMessage('Path "' . __FILE__ . '" does not point to a directory.');

        Filesystem\create_temporary_file(__FILE__);
    }

    public function testTemporaryFileThrowsForNonExistingDirectory(): void
    {
        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Directory "' . __FILE__ . '/foo" is not found.');

        Filesystem\create_temporary_file(__FILE__ . '/foo');
    }

    public function testTemporaryFileThrowsForPrefixWithSeparator(): void
    {
        $prefix = Str\join(['a', 'b'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$prefix should not contain a directory separator ( "' . Filesystem\SEPARATOR . '" ).',
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

    public function testFileModificationAndAccessTime(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        $modification_time = time() - 3600;
        $access_time = time() - 1800;

        Filesystem\create_file($file, $modification_time, $access_time);

        static::assertSame($modification_time, Filesystem\get_modification_time($file));
        static::assertSame($access_time, Filesystem\get_access_time($file));
    }

    public function testGetModificationTimeOfNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_modification_time($filename);
    }

    public function testGetAccessTimeOfNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_access_time($filename);
    }

    public function testGetChangeTimeOfNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_change_time($filename);
    }

    public function testGetInodeOfNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_inode($filename);
    }

    public function testFileSizeThrowsForNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('File "' . $filename . '" is not found.');

        Filesystem\file_size($filename);
    }

    public function testFileSizeThrowsForNonFile(): void
    {
        $this->expectException(Filesystem\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . $this->directory . '" does not point to a file.');

        Filesystem\file_size($this->directory);
    }

    public function testCopyThrowsForNonExistingFile(): void
    {
        $file = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('File "' . $file . '" is not found.');

        Filesystem\copy($file, '/foo/bar');
    }

    public function testCreateSymbolicLinkThrowsForNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\create_symbolic_link($filename, '/foo/bar');
    }

    public function testFileSizeThrowsForNonReadableFile(): void
    {
        $filename = Str\join([$this->directory, 'non-readable.txt'], Filesystem\SEPARATOR);
        Filesystem\create_file($filename);
        $permissions = Filesystem\get_permissions($filename) & 0o777;
        Filesystem\change_permissions($filename, 0o111);

        static::assertFalse(Filesystem\is_readable($filename));

        $this->expectException(Filesystem\Exception\NotReadableException::class);
        $this->expectExceptionMessage('File "' . $filename . '" is not readable.');

        try {
            Filesystem\file_size($filename);
        } finally {
            Filesystem\change_permissions($filename, $permissions);
        }
    }

    public function testCopyThrowsForNonReadableFile(): void
    {
        $file = Str\join([$this->directory, 'non-readable.txt'], Filesystem\SEPARATOR);
        Filesystem\create_file($file);
        $permissions = Filesystem\get_permissions($file) & 0o777;
        Filesystem\change_permissions($file, 0o111);

        static::assertFalse(Filesystem\is_readable($file));

        $this->expectException(Filesystem\Exception\NotReadableException::class);
        $this->expectExceptionMessage('File "' . $file . '" is not readable.');

        try {
            Filesystem\copy($file, '/foo/bar');
        } finally {
            Filesystem\change_permissions($file, $permissions);
        }
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

    public function testDeleteFileThrowsWhenFileIsNotFound(): void
    {
        $file = Str\join([$this->directory, 'deleted-one.txt'], Filesystem\SEPARATOR);

        static::assertFalse(Filesystem\is_file($file));

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('File "' . $file . '" is not found.');

        Filesystem\delete_file($file);
    }

    public function testDeleteFileThrowsWhenFileIsNotAFile(): void
    {
        $directory = Str\join([$this->directory, 'deleted-two'], Filesystem\SEPARATOR);

        Filesystem\create_directory($directory);

        static::assertTrue(Filesystem\exists($directory));
        static::assertTrue(Filesystem\is_directory($directory));
        static::assertFalse(Filesystem\is_file($directory));

        $this->expectException(Filesystem\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . $directory . '" does not point to a file.');

        Filesystem\delete_file($directory);
    }
}
