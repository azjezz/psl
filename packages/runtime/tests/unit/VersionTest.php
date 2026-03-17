<?php

declare(strict_types=1);

namespace Psl\Runtime\Tests\Unit;

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
        $versionDetails = Runtime\get_version_details();

        static::assertCount(4, $versionDetails);
        static::assertArrayHasKey('major', $versionDetails);
        static::assertArrayHasKey('minor', $versionDetails);
        static::assertArrayHasKey('release', $versionDetails);
        static::assertArrayHasKey('extra', $versionDetails);

        static::assertSame(
            [
                'major' => PHP_MAJOR_VERSION,
                'minor' => PHP_MINOR_VERSION,
                'release' => PHP_RELEASE_VERSION,
                'extra' => PHP_EXTRA_VERSION === '' ? null : PHP_EXTRA_VERSION,
            ],
            $versionDetails,
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
