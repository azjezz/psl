<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\StaticWindowSizeProvider;

final class StaticWindowSizeProviderTest extends TestCase
{
    public function testReturnsProvidedSize(): void
    {
        $provider = new StaticWindowSizeProvider(120, 40);

        static::assertSame([120, 40], $provider->get());
    }

    public function testReturnsSameValueOnRepeatedCalls(): void
    {
        $provider = new StaticWindowSizeProvider(80, 24);

        static::assertSame($provider->get(), $provider->get());
    }
}
