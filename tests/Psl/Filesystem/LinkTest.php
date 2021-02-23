<?php

declare(strict_types=1);

namespace Psl\Tests\Filesystem;

use Psl\Filesystem;
use Psl\Str;

final class LinkTest extends AbstractFilesystemTest
{
    protected string $function = 'link';

    public function testSymbolicLink(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $symlink = Str\join([$this->directory, 'symlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        Filesystem\create_symbolic_link($file, $symlink);

        static::assertTrue(Filesystem\exists($symlink));
        static::assertTrue(Filesystem\is_symbolic_link($symlink));

        static::assertSame($file, Filesystem\read_symbolic_link($symlink));
    }

    public function testSymbolicLinkAlreadyExists(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $symlink = Str\join([$this->directory, 'symlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        Filesystem\create_symbolic_link($file, $symlink);

        static::assertTrue(Filesystem\exists($symlink));
        static::assertTrue(Filesystem\is_symbolic_link($symlink));

        static::assertSame($file, Filesystem\read_symbolic_link($symlink));

        Filesystem\create_symbolic_link($file, $symlink);

        static::assertTrue(Filesystem\exists($symlink));
        static::assertTrue(Filesystem\is_symbolic_link($symlink));

        static::assertSame($file, Filesystem\read_symbolic_link($symlink));
    }

    public function testSymbolicLinkOverwrite(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $symbolic_link = Str\join([$this->directory, 'symbolic_link.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);
        Filesystem\create_file($symbolic_link);

        static::assertFalse(Filesystem\is_symbolic_link($symbolic_link));

        Filesystem\create_symbolic_link($file, $symbolic_link);

        static::assertTrue(Filesystem\is_symbolic_link($symbolic_link));
        static::assertSame($file, Filesystem\read_symbolic_link($symbolic_link));

        $file = Str\join([$this->directory, 'foo', 'bar'], Filesystem\SEPARATOR);
        $symbolic_link = Str\join([$this->directory, 'foo', 'baz'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);
        Filesystem\create_directory($symbolic_link);

        static::assertFalse(Filesystem\is_symbolic_link($symbolic_link));

        Filesystem\create_symbolic_link($file, $symbolic_link);

        static::assertTrue(Filesystem\is_symbolic_link($symbolic_link));
        static::assertSame($file, Filesystem\read_symbolic_link($symbolic_link));
    }

    public function testSymbolicLinkCreatesDestinationsDirectory(): void
    {
        self::runOnlyOnLinux();

        $directory = Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR);
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $symbolic_link = Str\join([$directory, 'symbolic.txt'], Filesystem\SEPARATOR);

        static::assertFalse(Filesystem\is_directory($directory));

        Filesystem\create_file($file);
        Filesystem\create_symbolic_link($file, $symbolic_link);

        static::assertTrue(Filesystem\is_directory($directory));
    }

    public function testHardLink(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$this->directory, 'hardlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        Filesystem\create_hard_link($file, $hardlink);

        static::assertTrue(Filesystem\exists($hardlink));
        static::assertFalse(Filesystem\is_symbolic_link($hardlink));

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));
    }

    public function testHardLinkAlreadyExists(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$this->directory, 'hardlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        Filesystem\create_hard_link($file, $hardlink);

        static::assertTrue(Filesystem\exists($hardlink));
        static::assertFalse(Filesystem\is_symbolic_link($hardlink));

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));

        Filesystem\create_hard_link($file, $hardlink);

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));
    }

    public function testHardLinkCreatesDestinationDirectory(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $destination_directory = Str\join([$this->directory, 'foo'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$destination_directory, 'hardlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        static::assertFalse(Filesystem\is_directory($destination_directory));

        Filesystem\create_hard_link($file, $hardlink);

        static::assertTrue(Filesystem\is_directory($destination_directory));
        static::assertTrue(Filesystem\exists($hardlink));
        static::assertFalse(Filesystem\is_symbolic_link($hardlink));

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));
    }

    public function testHardLinkOverwrite(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$this->directory, 'hardlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);
        Filesystem\create_file($hardlink);

        static::assertNotSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));

        Filesystem\create_hard_link($file, $hardlink);

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));

        $file = Str\join([$this->directory, 'foo', 'bar'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$this->directory, 'foo', 'baz'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);
        Filesystem\create_directory($hardlink);

        static::assertNotSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));

        Filesystem\create_hard_link($file, $hardlink);

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));
    }

    public function testHardLinkCreatesDestinationsDirectory(): void
    {
        $file = Str\join([$this->directory, 'write.txt'], Filesystem\SEPARATOR);
        $hardlink = Str\join([$this->directory, 'baz', 'hardlink.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($file);

        Filesystem\create_hard_link($file, $hardlink);

        static::assertSame(Filesystem\get_inode($file), Filesystem\get_inode($hardlink));
    }
}
