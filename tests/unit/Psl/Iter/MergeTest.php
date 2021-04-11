<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class MergeTest extends TestCase
{
    public function testMap(): void
    {
        $result = Iter\merge([1, 2], [9, 8]);
        
        $entries = Iter\to_array(Iter\enumerate($result));

        static::assertSame($entries[0], [0, 1]);
        static::assertSame($entries[1], [1, 2]);
        static::assertSame($entries[2], [0, 9]);
        static::assertSame($entries[3], [1, 8]);
    }
}
