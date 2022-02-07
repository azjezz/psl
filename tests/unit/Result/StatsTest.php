<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Result\Failure;
use Psl\Result\Stats;
use Psl\Result\Success;

final class StatsTest extends TestCase
{
    public function testEmpty(): void
    {
        $stats = new Stats();

        static::assertSame(0, $stats->total());
        static::assertSame(0, $stats->succeeded());
        static::assertSame(0, $stats->failed());
    }

    public function testStatsCollection(): void
    {
        $stats = new Stats();

        $new = $stats->apply(new Success(1));
        static::assertNotSame($stats, $new);
        static::assertSame(1, $new->total());
        static::assertSame(1, $new->succeeded());
        static::assertSame(0, $new->failed());

        $new = $new->apply(new Failure(new Exception('foo')));
        static::assertNotSame($stats, $new);
        static::assertSame(2, $new->total());
        static::assertSame(1, $new->succeeded());
        static::assertSame(1, $new->failed());
    }
}
