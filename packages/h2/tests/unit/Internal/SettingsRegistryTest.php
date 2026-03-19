<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\H2\Internal\SettingsRegistry;
use Psl\H2\Setting;

use const Psl\H2\DEFAULT_HEADER_TABLE_SIZE;
use const Psl\H2\DEFAULT_INITIAL_WINDOW_SIZE;
use const Psl\H2\DEFAULT_MAX_CONCURRENT_STREAMS;
use const Psl\H2\DEFAULT_MAX_FRAME_SIZE;
use const Psl\H2\DEFAULT_MAX_HEADER_LIST_SIZE;

final class SettingsRegistryTest extends TestCase
{
    public function testDefaultLocalValues(): void
    {
        $registry = new SettingsRegistry();

        static::assertSame(DEFAULT_HEADER_TABLE_SIZE, $registry->localValue(Setting::HeaderTableSize));
        static::assertSame(1, $registry->localValue(Setting::EnablePush));
        static::assertSame(DEFAULT_MAX_CONCURRENT_STREAMS, $registry->localValue(Setting::MaxConcurrentStreams));
        static::assertSame(DEFAULT_INITIAL_WINDOW_SIZE, $registry->localValue(Setting::InitialWindowSize));
        static::assertSame(DEFAULT_MAX_FRAME_SIZE, $registry->localValue(Setting::MaxFrameSize));
        static::assertSame(DEFAULT_MAX_HEADER_LIST_SIZE, $registry->localValue(Setting::MaxHeaderListSize));
    }

    public function testDefaultRemoteValues(): void
    {
        $registry = new SettingsRegistry();

        static::assertSame(DEFAULT_HEADER_TABLE_SIZE, $registry->remoteValue(Setting::HeaderTableSize));
        static::assertSame(DEFAULT_INITIAL_WINDOW_SIZE, $registry->remoteValue(Setting::InitialWindowSize));
    }

    public function testLocalOverrides(): void
    {
        $registry = new SettingsRegistry([
            Setting::MaxConcurrentStreams->value => 100,
            Setting::InitialWindowSize->value => 32_768,
        ]);

        static::assertSame(100, $registry->localValue(Setting::MaxConcurrentStreams));
        static::assertSame(32_768, $registry->localValue(Setting::InitialWindowSize));
        static::assertSame(DEFAULT_MAX_FRAME_SIZE, $registry->localValue(Setting::MaxFrameSize));
    }

    public function testApplyRemote(): void
    {
        $registry = new SettingsRegistry();

        $registry->applyRemote([
            Setting::MaxFrameSize->value => 32_768,
            Setting::MaxConcurrentStreams->value => 50,
        ]);

        static::assertSame(32_768, $registry->remoteValue(Setting::MaxFrameSize));
        static::assertSame(50, $registry->remoteValue(Setting::MaxConcurrentStreams));
    }

    public function testMarkLocalAcknowledged(): void
    {
        $registry = new SettingsRegistry();

        static::assertFalse($registry->isLocalAcknowledged());

        $registry->markLocalAcknowledged();

        static::assertTrue($registry->isLocalAcknowledged());
    }

    public function testLocalSettings(): void
    {
        $registry = new SettingsRegistry([
            Setting::MaxConcurrentStreams->value => 200,
        ]);

        $settings = $registry->localSettings();

        static::assertSame(200, $settings[Setting::MaxConcurrentStreams->value]);
        static::assertSame(DEFAULT_HEADER_TABLE_SIZE, $settings[Setting::HeaderTableSize->value]);
    }

    public function testLocalOverridesReturnsOnlyNonDefaults(): void
    {
        $registry = new SettingsRegistry([
            Setting::MaxConcurrentStreams->value => 200,
            Setting::HeaderTableSize->value => DEFAULT_HEADER_TABLE_SIZE,
        ]);

        $overrides = $registry->localOverrides();

        static::assertArrayHasKey(Setting::MaxConcurrentStreams->value, $overrides);
        static::assertSame(200, $overrides[Setting::MaxConcurrentStreams->value]);
    }

    public function testLocalOverridesEmptyWhenAllDefaults(): void
    {
        $registry = new SettingsRegistry();

        $overrides = $registry->localOverrides();

        static::assertSame([], $overrides);
    }
}
