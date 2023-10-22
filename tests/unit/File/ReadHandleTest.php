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
        $temporary_file = Filesystem\create_temporary_file();
        $handle = File\open_write_only($temporary_file);

        $handle->writeAll('hello');
        static::assertSame(2, $handle->write(', '));
        static::assertSame(6, $handle->tryWrite('world!'));

        $handle->close();

        $handle = File\open_read_only($temporary_file);
        $content = $handle->tryRead();

        static::assertSame('hello, world!', $content);
    }

    public function testNonExisting(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        $this->expectException(File\Exception\NotFoundException::class);
        File\open_read_only($temporary_file);
    }

    public function testNonFile(): void
    {
        $temporary_file = Filesystem\create_temporary_file();
        Filesystem\delete_file($temporary_file);

        $this->expectException(File\Exception\NotFileException::class);
        File\open_read_only(dirname($temporary_file));
    }
}
