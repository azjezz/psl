<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Filesystem;

use Psl\Filesystem;
use Psl\Str;

final class PermissionsTest extends AbstractFilesystemTest
{
    protected string $function = 'permissions';

    public function testChangePermissions(): void
    {
        $filename = Str\join([$this->directory, 'foo.txt'], Filesystem\SEPARATOR);

        Filesystem\create_file($filename);

        $permissions = Filesystem\get_permissions($filename) & 0o777;

        try {
            Filesystem\change_permissions($filename, 0o444);

            static::assertTrue(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0o222);

            static::assertTrue(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0o111);

            static::assertTrue(Filesystem\is_executable($filename));
            static::assertFalse(Filesystem\is_writable($filename));
            static::assertFalse(Filesystem\is_readable($filename));

            Filesystem\change_permissions($filename, 0o666);

            static::assertTrue(Filesystem\is_writable($filename));
            static::assertTrue(Filesystem\is_readable($filename));
            static::assertFalse(Filesystem\is_executable($filename));

            Filesystem\change_permissions($filename, 0o777);

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

        Filesystem\change_permissions($filename, 0o111);
    }

    public function testGetPermissionsThrowsForNonExistingFile(): void
    {
        $filename = Str\join([$this->directory, 'non-existing'], Filesystem\SEPARATOR);

        $this->expectException(Filesystem\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Node "' . $filename . '" is not found.');

        Filesystem\get_permissions($filename);
    }
}
