<?php

declare(strict_types=1);

namespace Psl\File\Tests\Unit;

use Override;
use PHPUnit\Framework\TestCase;

use function chmod;
use function fileperms;
use function is_dir;
use function is_link;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;

/**
 * Base test case that creates and cleans up a temporary directory.
 */
abstract class AbstractFileTestCase extends TestCase
{
    protected string $function;
    protected string $cacheDirectory;
    protected string $directory;
    private int $directoryPermissions;

    #[Override]
    protected function setUp(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Test can only be executed under *nix OS.');
        }

        $cacheDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.cache';

        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0o777, true);
        }

        $canonical = realpath($cacheDirectory);
        static::assertIsString($canonical);

        $this->cacheDirectory = $canonical;
        $this->directory = $this->cacheDirectory . DIRECTORY_SEPARATOR . $this->function;

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0o777, true);
        }

        $this->directoryPermissions = fileperms($this->directory) & 0o777;

        static::assertDirectoryExists($this->directory);
    }

    #[Override]
    protected function tearDown(): void
    {
        if (!isset($this->directory)) {
            return;
        }

        chmod($this->directory, $this->directoryPermissions);
        self::deleteDirectory($this->directory);

        static::assertDirectoryDoesNotExist($this->directory);
    }

    private static function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $entries = scandir($dir);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_link($path)) {
                unlink($path);
            } elseif (is_dir($path)) {
                self::deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
