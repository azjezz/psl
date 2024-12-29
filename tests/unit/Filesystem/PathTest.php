<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use PHPUnit\Framework\TestCase;
use Psl\Filesystem;

final class PathTest extends TestCase
{
    /**
     * @dataProvider providePathInformationData
     */
    public function testPathInformation(
        string $path,
        string $directory,
        string $basename,
        string $filename,
        null|string $extension,
    ): void {
        static::assertSame($directory, Filesystem\get_directory($path));
        static::assertSame($basename, Filesystem\get_basename($path));
        static::assertSame($filename, Filesystem\get_filename($path));
        static::assertSame($extension, Filesystem\get_extension($path));
    }

    public function providePathInformationData(): iterable
    {
        yield [
            '/home/azjezz/Projects/psl/src/bootstrap.php',
            '/home/azjezz/Projects/psl/src',
            'bootstrap.php',
            'bootstrap',
            'php',
        ];

        yield [
            '/home/azjezz/Projects/psl/src/bootstrap.',
            '/home/azjezz/Projects/psl/src',
            'bootstrap.',
            'bootstrap',
            null,
        ];

        yield [
            '/home/azjezz/Projects/psl/src/Psl',
            '/home/azjezz/Projects/psl/src',
            'Psl',
            'Psl',
            null,
        ];
    }

    public function testGetBasenameWithSuffix(): void
    {
        static::assertSame('bootstrap.p', Filesystem\get_basename('/home/azjezz/Projects/psl/src/bootstrap.php', 'hp'));
    }

    public function testGetDirectoryWithMultipleLevels(): void
    {
        static::assertSame('/home/azjezz/Projects', Filesystem\get_directory('/home/azjezz/Projects/psl/src/Psl', 3));
    }
}
