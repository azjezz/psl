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

    public function testWithInitialWindowSize(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withInitialWindowSize(2_097_152);

        static::assertSame(2_097_152, $new->initialWindowSize);
        static::assertSame(1_048_576, $config->initialWindowSize);
    }

    public function testWithMaxFrameSize(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxFrameSize(32_768);

        static::assertSame(32_768, $new->maxFrameSize);
        static::assertSame(16_384, $config->maxFrameSize);
    }

    public function testWithMaxHeaderBlockSize(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxHeaderBlockSize(131_072);

        static::assertSame(131_072, $new->maxHeaderBlockSize);
        static::assertSame(65_536, $config->maxHeaderBlockSize);
    }

    public function testWithMaxReceiveWindowSize(): void
    {
        $config = new H2ClientConfiguration();
        $new = $config->withMaxReceiveWindowSize(33_554_432);

        static::assertSame(33_554_432, $new->maxReceiveWindowSize);
        static::assertSame(16_777_216, $config->maxReceiveWindowSize);
    }

    public function testWithMethodsPreserveOtherFields(): void
    {
        $config = new H2ClientConfiguration(
            initialWindowSize: 500_000,
            maxFrameSize: 32_768,
            maxHeaderBlockSize: 100_000,
            maxConcurrentStreams: 50,
            maxReceiveWindowSize: 8_000_000,
        );

        $new = $config->withInitialWindowSize(999);
        static::assertSame(999, $new->initialWindowSize);
        static::assertSame(32_768, $new->maxFrameSize);
        static::assertSame(100_000, $new->maxHeaderBlockSize);
        static::assertSame(50, $new->maxConcurrentStreams);
        static::assertSame(8_000_000, $new->maxReceiveWindowSize);

        $new = $config->withMaxFrameSize(999);
        static::assertSame(500_000, $new->initialWindowSize);
        static::assertSame(999, $new->maxFrameSize);

        $new = $config->withMaxHeaderBlockSize(999);
        static::assertSame(500_000, $new->initialWindowSize);
        static::assertSame(999, $new->maxHeaderBlockSize);

        $new = $config->withMaxReceiveWindowSize(999);
        static::assertSame(500_000, $new->initialWindowSize);
        static::assertSame(999, $new->maxReceiveWindowSize);
    }

    public function testWithChaining(): void
    {
        $config = new H2ClientConfiguration()
            ->withInitialWindowSize(2_000_000)
            ->withMaxFrameSize(32_768)
            ->withMaxHeaderBlockSize(100_000)
            ->withMaxConcurrentStreams(50)
            ->withMaxConcurrentPushes(5)
            ->withMaxHeaderListSize(32_768)
            ->withMaxReceiveWindowSize(8_000_000);

        static::assertSame(2_000_000, $config->initialWindowSize);
        static::assertSame(32_768, $config->maxFrameSize);
        static::assertSame(100_000, $config->maxHeaderBlockSize);
        static::assertSame(50, $config->maxConcurrentStreams);
        static::assertSame(5, $config->maxConcurrentPushes);
        static::assertSame(32_768, $config->maxHeaderListSize);
        static::assertSame(8_000_000, $config->maxReceiveWindowSize);
    }
}
