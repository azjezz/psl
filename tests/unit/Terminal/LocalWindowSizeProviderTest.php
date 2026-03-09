<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\LocalWindowSizeProvider;

final class LocalWindowSizeProviderTest extends TestCase
{
    public function testReturnsPositiveDimensions(): void
    {
        $provider = new LocalWindowSizeProvider();

        [$cols, $rows] = $provider->get();

        static::assertGreaterThan(0, $cols);
        static::assertGreaterThan(0, $rows);
    }

    public function testReturnsTwoElementArray(): void
    {
        $provider = new LocalWindowSizeProvider();

        $result = $provider->get();

        static::assertCount(2, $result);
    }

    public function testConsecutiveCallsReturnSameSize(): void
    {
        $provider = new LocalWindowSizeProvider();

        static::assertSame($provider->get(), $provider->get());
    }
}
