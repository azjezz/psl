<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReindexTest extends TestCase
{
    public function testReindex(): void
    {
        $result = Iter\reindex([1, 2, 3], static fn (int $value): int => $value);
        
        static::assertSame([1 => 1, 2 => 2, 3 => 3], Iter\to_array_with_keys($result));
    }
}
