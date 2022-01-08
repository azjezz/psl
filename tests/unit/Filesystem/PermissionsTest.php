<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Filesystem;
use Psl\OS;
use Psl\Str;

final class PermissionsTest extends AbstractFilesystemTest
{
    protected string $function = 'permissions';

    public function testChangePermissions(): void
    {
        if (OS\is_windows()) {
            // executable bit on windows.
            static::markTestSkipped('Test can only be executed under *nix OS.');
        }

        $filename = Str\join([$this->directory, 'foo.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($filename);

        $permissions = Filesystem\get_permissions($filename) & 0777;

        try {
            Filesystem\change_permissions($filename, 0444);

            static::assertTrue(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0222);

            static::assertTrue(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0111);

            static::assertTrue(Filesystem\is_executable($filename));
            static::assertFalse(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_readable($filename));

            Filesystem\change_permissions($filename, 0666);

            static::assertTrue(Filesystem\is_writable($filename));
            static::assertTrue(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0777);

            static::assertTrue(Filesystem\is_writable($filename));
            static::assertTrue(Filesystem\is_readable($filename));
            static::assertTrue(Filesystem\is_executable($filename));
        } finally {
            Filesystem\change_permissions($filename, $permissions);
        }
    }

    public function testChangePermissionsThrowsForNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\change_permissions($filename, 0111);
    }

    public function testGetPermissionsThrowsForNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_permissions($filename);
    }
}
