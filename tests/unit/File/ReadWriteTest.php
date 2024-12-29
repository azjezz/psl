<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\OS;
use Psl\Str;
use Psl\Tests\Unit\Filesystem\AbstractFilesystemTest;

final class ReadWriteTest extends AbstractFilesystemTest
{
    protected string $function = 'file';

    public function testWriteFile(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        static::assertFileDoesNotExist($file);

        File\write($file, 'Hello!');

        static::assertFileExists($file);

        static::assertStringEqualsFile($file, 'Hello!');

        File\write($file, 'Hello', mode: File\WriteMode::Truncate);

        static::assertStringEqualsFile($file, 'Hello');

        File\write($file, ', World!', mode: File\WriteMode::Append);

        static::assertStringEqualsFile($file, 'Hello, World!');

        Filesystem\delete_file($file);
    }

    public function testWriteFileWithTruncateWriteModeCreatesFile(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);

        static::assertFileDoesNotExist($file);

        File\write($file, 'Hello!', File\WriteMode::Truncate);

        static::assertFileExists($file);

        static::assertStringEqualsFile($file, 'Hello!');

        Filesystem\delete_file($file);
    }

    public function testWriteFileClearsFileStat(): void
    {
        $file = Filesystem\create_temporary_file();

        File\write($file, 'Hello');

        static::assertSame(5, Filesystem\file_size($file));

        File\write($file, ', World!', mode: File\WriteMode::Append);

        static::assertSame(13, Filesystem\file_size($file));

        Filesystem\delete_file($file);
    }

    public function testWriteFileThrowsForDirectories(): void
    {
        $this->expectException(File\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . $this->directory . '" does not point to a file.');

        File\write($this->directory, 'hello');
    }

    public function testAppendFileThrowsForDirectories(): void
    {
        $this->expectException(File\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . $this->directory . '" does not point to a file.');

        File\write($this->directory, 'hello', mode: File\WriteMode::Append);
    }

    public function testReadFileThrowsForDirectories(): void
    {
        $this->expectException(File\Exception\NotFileException::class);
        $this->expectExceptionMessage('Path "' . $this->directory . '" does not point to a file.');

        File\read($this->directory);
    }

    public function testWriteFileThrowsForNonWritableFiles(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        Filesystem\create_file($file);
        $permissions = Filesystem\get_permissions($file) & 0o777;
        Filesystem\change_permissions($file, 0o111);

        try {
            $this->expectException(File\Exception\NotWritableException::class);
            $this->expectExceptionMessage('File "' . $file . '" is not writable.');

            File\write($file, 'hello');
        } finally {
            Filesystem\change_permissions($file, $permissions);
        }
    }

    public function testRead(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        File\write($file, 'PHP Standard Library');
        File\write($file, ' - a modern, consistent, centralized', mode: File\WriteMode::Append);
        File\write($file, ' well-typed set of APIs for PHP programmers.', mode: File\WriteMode::Append);

        $content = File\read($file, 0, 20);

        static::assertSame('PHP Standard Library', $content);

        $content = File\read($file, 84, 16);

        static::assertSame('PHP programmers.', $content);
    }

    public function testThrowsWhenDirectoryCreationFails(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $target_directory = Env\temp_dir() . DIRECTORY_SEPARATOR . 'you-shall-not-pass';
        Filesystem\create_directory($target_directory, 0o000);

        $target_file =
            $target_directory . DIRECTORY_SEPARATOR . 'fails-on-subdir-creation' . DIRECTORY_SEPARATOR . 'somefile.txt';

        $this->expectException(File\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create the directory for file "' . $target_file . '".');

        new File\ReadWriteHandle($target_file, File\WriteMode::MustCreate);
    }
}
