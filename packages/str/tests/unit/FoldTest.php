<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FoldTest extends TestCase
{
    public function testFold(): void
    {
        static::assertSame('ssoo', Str\fold('ẞOO'));
    }
}
