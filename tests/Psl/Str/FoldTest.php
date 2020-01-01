<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class FoldTest extends TestCase
{
    public function testFold(): void
    {
        self::assertSame('ssoo', Str\fold('ẞOO'));
    }
}
