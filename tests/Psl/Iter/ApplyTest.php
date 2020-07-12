<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection\MutableVector;
use Psl\Iter;

class ApplyTest extends TestCase
{
    public function testApply(): void
    {
        $vec = new MutableVector([]);
        Iter\apply([1, 2, 3], fn (int $i) => $vec->add($i));

        self::assertSame([1, 2, 3], $vec->toArray());
    }
}
