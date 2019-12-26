<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class LengthTest extends TestCase
{
    public function testLength():void
    {
        self::assertSame(6, Str\length('azjezz'));
        self::assertSame(4, Str\length('تونس'));
        self::assertSame(3, Str\length('سيف'));
    }
}
