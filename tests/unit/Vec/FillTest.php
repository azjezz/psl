<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class FillTest extends TestCase
{
    public function testFill(): void
    {
        $result = Vec\fill(5, 42);

        static::assertSame([42, 42, 42, 42, 42], $result);
    }
}
