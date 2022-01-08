<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\SecureRandom;

final class WriteHandleTest extends TestCase
{
    public function testMustCreateExistingFile(): void
    {
        $this->expectException(File\Exception\AlreadyCreatedException::class);
        $this->expectExceptionMessage('already created.');

        new File\WriteHandle(__FILE__, File\WriteMode::MUST_CREATE);
    }

    public function testAppendToNonExistingFile(): void
    {
        $this->expectException(File\Exception\NotFoundException::class);
        $this->expectExceptionMessage('is not found.');

        $f = new File\WriteHandle(Env\temp_dir() . '/' . SecureRandom\string(20), File\WriteMode::APPEND);
        $f->write('g');
    }

    public function testAppendToANonWritableFile(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\change_permissions($temporary_file, 0555);

        $this->expectException(File\Exception\NotWritableException::class);
        $this->expectExceptionMessage('is not writable.');

        new File\WriteHandle($temporary_file, File\WriteMode::APPEND);
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

    public function testCreateNonExisting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        static::assertFalse(Filesystem\is_file($temporary_file));

        $handle = new File\WriteHandle($temporary_file, File\WriteMode::MUST_CREATE);
        $handle->close();

        static::assertTrue(Filesystem\is_file($temporary_file));
    }
}
