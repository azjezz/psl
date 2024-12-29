<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class ReindexTest extends TestCase
{
    public function testReindex(): void
    {
        $result = Dict\reindex([1, 2, 3], static fn(int $value): int => $value);

        static::assertSame([1 => 1, 2 => 2, 3 => 3], $result);
    }
}
