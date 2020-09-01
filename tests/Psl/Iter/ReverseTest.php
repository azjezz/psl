<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ReverseTest extends TestCase
{
    public function testReverse(): void
    {
       $result =  Iter\reverse(['foo', 'bar', 'baz', 'qux']);

        self::assertSame(['qux', 'baz', 'bar', 'foo'], Iter\to_array_with_keys($result));
    }

    public function testReverseEarlyReturnForEmptyIterables(): void
    {
        $result = Iter\reverse(Iter\to_iterator([]));

        self::assertSame([], Iter\to_array($result));
    }
}
