<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ReindexTest extends TestCase
{
    public function testReindex(): void
    {
        $result = Iter\reindex([1, 2, 3], fn (int $value): int => $value);
        
        self::assertSame([1 => 1, 2 => 2, 3 => 3], Iter\to_array_with_keys($result));
    }
}
