<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ReverseTest extends TestCase
{
    public function testReverse(): void
    {
        $result = Vec\reverse::<string>(['foo', 'bar', 'baz', 'qux']);

        static::assertSame(['qux', 'baz', 'bar', 'foo'], $result);
    }

    public function testReverseEarlyReturnForEmptyIterables(): void
    {
        $result = Vec\reverse::<int>(Iter\to_iterator::<int, int>([]));

        static::assertSame([], $result);
    }
}
