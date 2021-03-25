<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class UniqueScalarTest extends TestCase
{

    public function testUniqueScalars(): void
    {
        $array   = Vec\fill(10, 'foo');
        $array[] = 'bar';

        $unique = Dict\unique_scalar($array);

        static::assertCount(2, $unique);
        static::assertSame('foo', Iter\first($unique));
    }
}
