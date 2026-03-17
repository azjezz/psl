<?php

declare(strict_types=1);

namespace Psl\Fun\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Fun;

final class IdentityTest extends TestCase
{
    public function testIdentity(): void
    {
        $expected = 'x';
        $identity = Fun\identity();

        static::assertSame($expected, $identity($expected));
    }
}
