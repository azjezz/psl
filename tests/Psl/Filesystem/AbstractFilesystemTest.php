<?php

declare(strict_types=1);

namespace Psl\Tests\Filesystem;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Filesystem;
use Psl\Str;
use Psl\Type;

use const PHP_OS_FAMILY;

abstract class AbstractFilesystemTest extends TestCase
{
    protected string $function;
    protected string $cacheDirectory;
    protected string $directory;
    private int $directoryPermissions;

    protected function setUp(): void
    {
        $this->cacheDirectory = Type\string()->assert(Filesystem\canonicalize(Str\join([
            __DIR__, '..', '..', '.cache'
        ], Filesystem\SEPARATOR)));

        $this->directory = Str\join([$this->cacheDirectory, $this->function], Filesystem\SEPARATOR);
        Filesystem\create_directory($this->directory);
        $this->directoryPermissions = Filesystem\get_permissions($this->directory) & 0777;

        static::assertTrue(Filesystem\exists($this->directory));
        static::assertTrue(Filesystem\is_directory($this->directory));
    }

    protected function tearDown(): void
    {
        Filesystem\change_permissions($this->directory, $this->directoryPermissions);
        Filesystem\delete_directory($this->directory, true);

        static::assertFalse(Filesystem\is_directory($this->directory));
    }

    protected static function runOnlyOnLinux(): void
    {
        if ('Linux' !== PHP_OS_FAMILY) {
            static::markTestSkipped('Test can only be executed on linux.');
        }
    }

    protected static function runOnlyUsingRoot(): void
    {
        $user = Env\get_var('USER');
        if (null === $user || 'root' === $user) {
            return;
        }

        static::markTestSkipped('Test can only be executed by a superuser.');
    }
}
