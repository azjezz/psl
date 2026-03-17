<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\TLS\Version;

final class VersionTest extends TestCase
{
    public static function provideVersions(): iterable
    {
        yield [Version::Tls10, 0];
        yield [Version::Tls11, 1];
        yield [Version::Tls12, 2];
        yield [Version::Tls13, 3];
    }

    #[DataProvider('provideVersions')]
    public function testVersionValues(Version $version, int $expected): void
    {
        static::assertSame($expected, $version->value);
    }

    public function testVersionOrdering(): void
    {
        static::assertLessThan(Version::Tls11->value, Version::Tls10->value);
        static::assertLessThan(Version::Tls12->value, Version::Tls11->value);
        static::assertLessThan(Version::Tls13->value, Version::Tls12->value);
    }

    public function testDefault(): void
    {
        static::assertSame(Version::Tls13, Version::default());
    }
}
