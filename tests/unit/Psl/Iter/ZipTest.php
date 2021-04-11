<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ZipTest extends TestCase
{
    public function testZip(): void
    {
        $result = Iter\zip(
            [1, 2, 3, 4],
            [5, 6, 7, 8, 9],
            [10, 11, 12]
        );

        static::assertCount(3, $result);

        $k = $result->key();
        $v = $result->current();
        static::assertSame([0, 0, 0], $k);
        static::assertSame([1, 5, 10], $v);

        $result->next();

        $k = $result->key();
        $v = $result->current();
        static::assertSame([1, 1, 1], $k);
        static::assertSame([2, 6, 11], $v);

        $result->next();

        $k = $result->key();
        $v = $result->current();
        static::assertSame([2, 2, 2], $k);
        static::assertSame([3, 7, 12], $v);
    }
    
    public function testZipWithZeroIterables(): void
    {
        $result = Iter\zip(...[]);
        
        static::assertCount(0, $result);
    }
}
