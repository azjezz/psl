<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI\PathKind;

final class PathKindTest extends TestCase
{
    public function testAbsoluteCaseExists(): void
    {
        static::assertSame('Absolute', PathKind::Absolute->name);
    }

    public function testRootlessCaseExists(): void
    {
        static::assertSame('Rootless', PathKind::Rootless->name);
    }

    public function testNoneCaseExists(): void
    {
        static::assertSame('None', PathKind::None->name);
    }

    public function testExactlyThreeCases(): void
    {
        static::assertCount(3, PathKind::cases());
    }
}
