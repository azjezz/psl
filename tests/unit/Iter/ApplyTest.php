<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection\MutableVector;
use Psl\Iter;

final class ApplyTest extends TestCase
{
    public function testApply(): void
    {
        $vec = new MutableVector([]);
        Iter\apply([1, 2, 3], static fn(int $i) => $vec->add($i));

        static::assertSame([1, 2, 3], $vec->toArray());
    }
}
