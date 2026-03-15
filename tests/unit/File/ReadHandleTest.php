<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;

final class ReadHandleTest extends TestCase
{
    public function testWriting(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        $handle = File\open_write_only($temporaryFile);

        $handle->writeAll('hello');
        static::assertSame(2, $handle->write(', '));
        static::assertSame(6, $handle->tryWrite('world!'));

        $handle->close();

        $handle = File\open_read_only($temporaryFile);
        $content = $handle->tryRead();

        static::assertSame('hello, world!', $content);
    }

    public function testNonExisting(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporaryFile);

        $this->expectException(File\Exception\NotFoundException::class);
        File\open_read_only($temporaryFile);
    }

    public function testNonFile(): void
    {
        $temporaryFile = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporaryFile);

        $this->expectException(File\Exception\NotFileException::class);
        File\open_read_only(dirname($temporaryFile));
    }
}
