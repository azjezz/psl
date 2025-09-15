<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Env;
use Psl\Filesystem;
use Psl\Str;
use Psl\Vec;

final class DirectoryTest extends AbstractFilesystemTest
{
    protected string $function = 'directory';

    public function testReadDirectory(): void
    {
        Filesystem\create_file(Str\join(
            [
                $this->directory,
                'hello.txt',
            ],
            Filesystem\SEPARATOR,
        ));

        Filesystem\create_directory(Str\join(
            [
                $this->directory,
                'foo',
            ],
            Filesystem\SEPARATOR,
        ));

        $children = Filesystem\read_directory($this->directory);

        static::assertCount(2, $children);
        static::assertSame(
            [
                Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR),
                Str\join([$this->directory, 'hello.txt'], Filesystem\SEPARATOR),
            ],
            Vec\sort($children),
        );
    }

    public function testReadDirectoryThrowsIfDirectoryDoesNotExist(): void
    {
        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Directory "' . Env\temp_dir() . '/foo-bar-baz' . '" is not found.');

        Filesystem\read_directory(Env\temp_dir() . '/foo-bar-baz');
    }

    public function testReadDirectoryThrowsIfNotDirectory(): void
    {
        $filename = Str\join(
            [
                $this->directory,
                'hello.txt',
            ],
            Filesystem\SEPARATOR,
        );

        Filesystem\create_file($filename);

        $this->expectException(Filesystem\Exception\NotDirectoryException::class);
        $this->expectExceptionMessage('Path "' . $filename . '" does not point to a directory.');

        Filesystem\read_directory($filename);
    }

    public function testReadDirectoryThrowsIfNotReadable(): void
    {
        Filesystem\change_permissions($this->directory, 0o077);

        $this->expectException(Filesystem\Exception\NotReadableException::class);
        $this->expectExceptionMessage('Directory "' . $this->directory . '" is not readable.');

        try {
            Filesystem\read_directory($this->directory);
        } finally {
            // restore $this->directory permissions, otherwise we won't
            // be able to delete it.
            Filesystem\change_permissions($this->directory, 0o777);
        }
    }

    public function testDeleteDirectory(): void
    {
        $directory = Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR);
        Filesystem\create_directory($directory);

        static::assertTrue(Filesystem\is_directory($directory));

        Filesystem\delete_directory($directory);

        static::assertFalse(Filesystem\is_directory($directory));
    }

    public function testDeleteDirectoryThrowsWhenDirectoryIsNotFound(): void
    {
        $directory = Str\join([$this->directory, 'bar'], Filesystem\SEPARATOR);

        static::assertFalse(Filesystem\is_directory($directory));

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Directory "' . $directory . '" is not found.');

        Filesystem\delete_directory($directory);
    }

    public function testDeleteDirectoryThrowsWhenDirectoryIsNotADirectory(): void
    {
        $file = Str\join([$this->directory, 'baz.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        static::assertTrue(Filesystem\exists($file));
        static::assertTrue(Filesystem\is_file($file));
        static::assertFalse(Filesystem\is_directory($file));

        $this->expectException(Filesystem\Exception\NotDirectoryException::class);
        $this->expectExceptionMessage('Path "' . $file . '" does not point to a directory.');

        Filesystem\delete_directory($file);
    }
}
