<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Env;
use Psl\Exception\InvariantViolationException;
use Psl\Filesystem;
use Psl\Str;
use Psl\Vec;

use const PHP_OS_FAMILY;

final class ReadDirectoryTest extends AbstractFilesystemTest
{
    protected string $function = 'read_directory';

    public function testReadDirectory(): void
    {
        Filesystem\create_file(Str\join([
            $this->directory, 'hello.txt'
        ], Filesystem\SEPARATOR));

        Filesystem\create_directory(Str\join([
            $this->directory, 'foo'
        ], Filesystem\SEPARATOR));

        $children = Filesystem\read_directory($this->directory);

        static::assertCount(2, $children);
        static::assertSame([
            Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR),
            Str\join([$this->directory, 'hello.txt'], Filesystem\SEPARATOR),
        ], Vec\sort($children));
    }

    public function testReadDirectoryThrowsIfDirectoryDoesNotExist(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Directory "' . Env\temp_dir() . '/foo-bar-baz' . '" does not exist.');

        Filesystem\read_directory(Env\temp_dir() . '/foo-bar-baz');
    }

    public function testReadDirectoryThrowsIfNotDirectory(): void
    {
        $filename = Str\join([
            $this->directory, 'hello.txt'
        ], Filesystem\SEPARATOR);

        Filesystem\create_file($filename);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Directory "' . $filename . '" is not a directory.');

        Filesystem\read_directory($filename);
    }

    public function testReadDirectoryThrowsIfNotReadable(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // executable bit on windows.
            static::markTestSkipped('Test can only be executed under *nix OS.');
        }

        Filesystem\change_permissions($this->directory, 0077);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Directory "' . $this->directory . '" is not readable.');

        try {
            Filesystem\read_directory($this->directory);
        } finally {
            // restore $this->directory permissions, otherwise we won't
            // be able to delete it.
            Filesystem\change_permissions($this->directory, 0777);
        }
    }
}
