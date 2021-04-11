<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ReverseTest extends TestCase
{
    public function testReverse(): void
    {
        $result = Vec\reverse(['foo', 'bar', 'baz', 'qux']);

        static::assertSame(['qux', 'baz', 'bar', 'foo'], $result);
    }

    public function testReverseEarlyReturnForEmptyIterables(): void
    {
        $result = Vec\reverse(Iter\to_iterator([]));

        static::assertSame([], $result);
    }
}
