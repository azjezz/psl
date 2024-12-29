<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\OS;

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
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = new File\WriteHandle($temporary_file, File\WriteMode::Append);
        $handle->close();

        static::assertTrue(Filesystem\is_file($temporary_file));
    }

    public function testAppendToANonWritableFile(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporary_file, 0o555);

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('is not writable.');

        new File\WriteHandle($temporary_file, File\WriteMode::Append);
    }

    public function testWriting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        $handle = File\open_write_only($temporary_file);

        $handle->writeAll('hello');
        static::assertSame(2, $handle->write(', '));
        static::assertSame(6, $handle->tryWrite('world!'));

        $handle->close();

        $handle = File\open_read_only($temporary_file);
        $content = $handle->readAll();

        static::assertSame('hello, world!', $content);
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

        new File\WriteHandle($file, File\WriteMode::MustCreate);
    }

    public function testCreateNonExisting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = new File\WriteHandle($temporary_file, File\WriteMode::MustCreate);
        $handle->close();

        static::assertTrue(Filesystem\is_file($temporary_file));
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

        new File\WriteHandle($target_file, File\WriteMode::MustCreate);
    }
}
