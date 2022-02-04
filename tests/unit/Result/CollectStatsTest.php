<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Result;
use Psl\Result\Failure;
use Psl\Result\Success;

final class CollectStatsTest extends TestCase
{
    public function testWithStatsResult(): void
    {
        $success = new Success(1);
        $failure = new Failure(new Exception('foo'));

        $stats = Result\collect_stats([$success, $success, $failure]);

        static::assertSame(3, $stats->total());
        static::assertSame(2, $stats->succeeded());
        static::assertSame(1, $stats->failed());
    }
}
