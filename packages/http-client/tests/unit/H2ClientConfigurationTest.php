<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\H2ClientConfiguration;

final class H2ClientConfigurationTest extends TestCase
{
    public function testWithMaxConcurrentStreams(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxConcurrentStreams(50);

        static::assertSame(50, $new->maxConcurrentStreams);
        static::assertSame(100, $config->maxConcurrentStreams);
    }

    public function testWithMaxConcurrentPushes(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxConcurrentPushes(5);

        static::assertSame(5, $new->maxConcurrentPushes);
        static::assertSame(10, $config->maxConcurrentPushes);
    }

    public function testWithMaxHeaderListSize(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxHeaderListSize(32_768);

        static::assertSame(32_768, $new->maxHeaderListSize);
        static::assertSame(16_384, $config->maxHeaderListSize);
    }

    public function testWithChaining(): void
    {
        $config = new H2ClientConfiguration()
            ->withMaxConcurrentStreams(50)
            ->withMaxConcurrentPushes(5)
            ->withMaxHeaderListSize(32_768);

        static::assertSame(50, $config->maxConcurrentStreams);
        static::assertSame(5, $config->maxConcurrentPushes);
        static::assertSame(32_768, $config->maxHeaderListSize);
    }
}
