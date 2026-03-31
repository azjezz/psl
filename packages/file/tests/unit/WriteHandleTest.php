<?php

declare(strict_types=1);

namespace Psl\File\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\OS;

use const DIRECTORY_SEPARATOR;

final class WriteHandleTest extends TestCase
{
    public function testMustCreateExistingFile(): void
    {
        $this->expectException(File\Exception\AlreadyCreatedException::class);
        $this->expectExceptionMessage('already created.');

        new File\WriteHandle(__FILE__, File\WriteMode::MustCreate);
    }

    public function testAppendToNonExistingFile(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporaryFile);

        static::assertFalse(Filesystem\is_file($temporaryFile));

        $handle = new File\WriteHandle($temporaryFile, File\WriteMode::Append);
        $handle->close();

        static::assertTrue(Filesystem\is_file($temporaryFile));
    }

    public function testAppendToANonWritableFile(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporaryFile, 0o555);

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('is not writable.');

        new File\WriteHandle($temporaryFile, File\WriteMode::Append);
    }

    public function testWriting(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        $handle = File\open_write_only($temporaryFile);

        $handle->writeAll('hello');
        static::assertSame(2, $handle->write(', '));
        static::assertSame(6, $handle->tryWrite('world!'));

        $handle->close();

        $handle = File\open_read_only($temporaryFile);
        $content = $handle->readAll();

        static::assertSame('hello, world!', $content);
    }

    public function testThrowsWhenCreatingFile(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporaryFile);
        Filesystem\create_directory($temporaryFile);
        Filesystem\change_permissions($temporaryFile, 0o555);

        $file = $temporaryFile . Filesystem\SEPARATOR . 'foo';

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('File "' . $file . '" is not writable.');

        new File\WriteHandle($file, File\WriteMode::MustCreate);
    }

    public function testCreateNonExisting(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporaryFile);

        static::assertFalse(Filesystem\is_file($temporaryFile));

        $handle = new File\WriteHandle($temporaryFile, File\WriteMode::MustCreate);
        $handle->close();

        static::assertTrue(Filesystem\is_file($temporaryFile));
    }

    public function testThrowsWhenDirectoryCreationFails(): void
    {
        if (OS\is_windows()) {
            static::markTestSkipped('Permissions are not reliable on windows.');
        }

        $targetDirectory = Env\temp_dir() . DIRECTORY_SEPARATOR . 'you-shall-not-pass';
        Filesystem\create_directory($targetDirectory, 0o000);

        $targetFile =
            $targetDirectory . DIRECTORY_SEPARATOR . 'fails-on-subdir-creation' . DIRECTORY_SEPARATOR . 'somefile.txt';

        $this->expectException(File\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create the directory for file "' . $targetFile . '".');

        new File\WriteHandle($targetFile, File\WriteMode::MustCreate);
    }
}
