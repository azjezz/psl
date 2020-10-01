<?php

declare(strict_types=1);

namespace Psl\Tests\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;

class PassthroughTest extends TestCase
{
    public function testPassthrough(): void
    {
        $expected = 'x';
        $passthrough = Fun\passthrough();

        self::assertSame($expected, $passthrough($expected));
    }
}
