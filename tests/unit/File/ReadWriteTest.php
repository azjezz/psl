<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use Psl\Exception\InvariantViolationException;
use Psl\File;
use Psl\Filesystem;
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

        File\write($file, 'Hello', mode: File\WriteMode::TRUNCATE);

        static::assertStringEqualsFile($file, 'Hello');

        File\write($file, ', World!', mode: File\WriteMode::APPEND);

        static::assertStringEqualsFile($file, 'Hello, World!');

        Filesystem\delete_file($file);
    }

    public function testWriteFileClearsFileStat(): void
    {
        $file = Filesystem\create_temporary_file();

        File\write($file, 'Hello');

        static::assertSame(5, Filesystem\file_size($file));

        File\write($file, ', World!', mode: File\WriteMode::APPEND);

        static::assertSame(13, Filesystem\file_size($file));

        Filesystem\delete_file($file);
    }

    public function testWriteFileThrowsForDirectories(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('File "' . $this->directory . '" is not a file.');

        File\write($this->directory, 'hello');
    }

    public function testAppendFileThrowsForDirectories(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('File "' . $this->directory . '" is not a file.');

        File\write($this->directory, 'hello', mode: File\WriteMode::APPEND);
    }

    public function testReadFileThrowsForDirectories(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('File "' . $this->directory . '" is not a file.');

        File\read($this->directory);
    }

    public function testWriteFileThrowsForNonWritableFiles(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        Filesystem\create_file($file);
        $permissions = Filesystem\get_permissions($file) & 0777;
        Filesystem\change_permissions($file, 0111);

        try {
            $this->expectException(InvariantViolationException::class);
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
        File\write($file, ' - a modern, consistent, centralized', mode: File\WriteMode::APPEND);
        File\write($file, ' well-typed set of APIs for PHP programmers.', mode: File\WriteMode::APPEND);

        $content = File\read($file, 0, 20);

        static::assertSame('PHP Standard Library', $content);

        $content = File\read($file, 84, 16);

        static::assertSame('PHP programmers.', $content);
    }
}
