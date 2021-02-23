<?php

declare(strict_types=1);

namespace Psl\Tests\Filesystem;

use Psl\Filesystem;
use Psl\Str;

final class CopyTest extends AbstractFilesystemTest
{
    protected string $function = 'copy';

    public function testCopy(): void
    {
        $text_file = Str\join([$this->directory, 'hello.txt'], Filesystem\SEPARATOR);
        $markdown_file = Str\join([$this->directory, 'hello.md'], Filesystem\SEPARATOR);

        Filesystem\write_file($text_file, 'Hello, World!');
        Filesystem\copy($text_file, $markdown_file);

        static::assertSame('Hello, World!', Filesystem\read_file($markdown_file));
    }

    public function testCopyOverwrite(): void
    {
        $text_file = Str\join([$this->directory, 'hello.txt'], Filesystem\SEPARATOR);
        $markdown_file = Str\join([$this->directory, 'hello.md'], Filesystem\SEPARATOR);

        Filesystem\write_file($text_file, 'Hello, World!');
        Filesystem\write_file($markdown_file, '# Hello, World!');
        Filesystem\copy($text_file, $markdown_file);

        static::assertSame('Hello, World!', Filesystem\read_file($text_file));
        static::assertSame('# Hello, World!', Filesystem\read_file($markdown_file));

        Filesystem\copy($text_file, $markdown_file, true);

        static::assertSame('Hello, World!', Filesystem\read_file($text_file));
        static::assertSame('Hello, World!', Filesystem\read_file($markdown_file));
    }

    public function testCopyExecutableBits(): void
    {
        $shell_file = Str\join([$this->directory, 'hello.sh'], Filesystem\SEPARATOR);

        Filesystem\create_file($shell_file);
        Filesystem\change_permissions($shell_file, 0557);

        static::assertTrue(Filesystem\is_executable($shell_file));

        $shell_file_copy = Str\join([$this->directory, 'hey.sh'], Filesystem\SEPARATOR);

        Filesystem\copy($shell_file, $shell_file_copy);

        static::assertTrue(Filesystem\is_executable($shell_file_copy));
    }
}
