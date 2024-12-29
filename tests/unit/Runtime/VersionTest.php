<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;
use const PHP_VERSION;
use const PHP_VERSION_ID;

final class VersionTest extends TestCase
{
    public function testGetVersionDetails(): void
    {
        $version_details = Runtime\get_version_details();

        static::assertCount(4, $version_details);
        static::assertArrayHasKey('major', $version_details);
        static::assertArrayHasKey('minor', $version_details);
        static::assertArrayHasKey('release', $version_details);
        static::assertArrayHasKey('extra', $version_details);

        static::assertSame(
            [
                'major' => PHP_MAJOR_VERSION,
                'minor' => PHP_MINOR_VERSION,
                'release' => PHP_RELEASE_VERSION,
                'extra' => PHP_EXTRA_VERSION ?: null,
            ],
            $version_details,
        );
    }

    public function testGetVersionId(): void
    {
        static::assertSame(PHP_VERSION_ID, Runtime\get_version_id());
    }

    public function testGetVersion(): void
    {
        static::assertSame(PHP_VERSION, Runtime\get_version());
    }
}
