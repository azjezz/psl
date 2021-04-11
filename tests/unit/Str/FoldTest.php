<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FoldTest extends TestCase
{
    public function testFold(): void
    {
        static::assertSame('ssoo', Str\fold('ẞOO'));
    }
}
