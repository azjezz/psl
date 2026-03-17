<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Endianness;

final class EndiannessTest extends TestCase
{
    public function testDefaultIsBig(): void
    {
        static::assertSame(Endianness::Big, Endianness::default());
    }
}
