<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime;
use Psl\H2\Exception\ProtocolException;
use Psl\H2\Frame\FrameType;
use Psl\H2\RateLimiter;

final class RateLimiterTest extends TestCase
{
    public function testAllowsFramesWithinLimit(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [5, DateTime\Duration::seconds(10)],
        ]);

        for ($i = 0; $i < 5; $i++) {
            $limiter->record(FrameType::Ping->value);
        }

        static::assertTrue(true);
    }

    public function testThrowsWhenLimitExceeded(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [3, DateTime\Duration::seconds(10)],
        ]);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        for ($i = 0; $i < 4; $i++) {
            $limiter->record(FrameType::Ping->value);
        }
    }

    public function testIgnoresUnlimitedFrameTypes(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [3, DateTime\Duration::seconds(10)],
        ]);

        for ($i = 0; $i < 100; $i++) {
            $limiter->record(FrameType::Data->value);
        }

        static::assertTrue(true);
    }

    public function testMultipleFrameTypesTrackedIndependently(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [3, DateTime\Duration::seconds(10)],
            FrameType::Settings->value => [5, DateTime\Duration::seconds(10)],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $limiter->record(FrameType::Ping->value);
        }

        for ($i = 0; $i < 5; $i++) {
            $limiter->record(FrameType::Settings->value);
        }

        static::assertTrue(true);
    }

    public function testDefault(): void
    {
        $limiter = RateLimiter::default();

        for ($i = 0; $i < 50; $i++) {
            $limiter->record(FrameType::Ping->value);
        }

        static::assertTrue(true);
    }

    public function testDefaultExceedsLimit(): void
    {
        $limiter = RateLimiter::default();

        $this->expectException(ProtocolException::class);

        for ($i = 0; $i < 51; $i++) {
            $limiter->record(FrameType::Ping->value);
        }
    }

    public function testEmptyDataRecording(): void
    {
        $limiter = new RateLimiter([
            -1 => [3, DateTime\Duration::seconds(10)],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $limiter->record(-1);
        }

        static::assertTrue(true);
    }

    public function testEmptyDataExceedsLimit(): void
    {
        $limiter = new RateLimiter([
            -1 => [2, DateTime\Duration::seconds(10)],
        ]);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('empty DATA');

        for ($i = 0; $i < 3; $i++) {
            $limiter->record(-1);
        }
    }

    public function testDefaultSettingsLimitExceeded(): void
    {
        $limiter = RateLimiter::default();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Settings');

        for ($i = 0; $i < 101; $i++) {
            $limiter->record(FrameType::Settings->value);
        }
    }

    public function testDefaultRstStreamLimitExceeded(): void
    {
        $limiter = RateLimiter::default();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('RstStream');

        for ($i = 0; $i < 101; $i++) {
            $limiter->record(FrameType::RstStream->value);
        }
    }

    public function testDefaultPriorityLimitExceeded(): void
    {
        $limiter = RateLimiter::default();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Priority');

        for ($i = 0; $i < 101; $i++) {
            $limiter->record(FrameType::Priority->value);
        }
    }

    public function testDefaultEmptyDataLimitExceeded(): void
    {
        $limiter = RateLimiter::default();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('empty DATA');

        for ($i = 0; $i < 101; $i++) {
            $limiter->record(-1);
        }
    }

    public function testDefaultSettingsLimit(): void
    {
        $limiter = RateLimiter::default();

        for ($i = 0; $i < 100; $i++) {
            $limiter->record(FrameType::Settings->value);
        }

        static::assertTrue(true);
    }

    public function testDefaultRstStreamLimit(): void
    {
        $limiter = RateLimiter::default();

        for ($i = 0; $i < 100; $i++) {
            $limiter->record(FrameType::RstStream->value);
        }

        static::assertTrue(true);
    }

    public function testDefaultPriorityLimit(): void
    {
        $limiter = RateLimiter::default();

        for ($i = 0; $i < 100; $i++) {
            $limiter->record(FrameType::Priority->value);
        }

        static::assertTrue(true);
    }

    public function testDefaultEmptyDataLimit(): void
    {
        $limiter = RateLimiter::default();

        for ($i = 0; $i < 100; $i++) {
            $limiter->record(-1);
        }

        static::assertTrue(true);
    }

    public function testWindowResetsAfterDurationExpires(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [10, DateTime\Duration::milliseconds(1)],
        ]);

        $limiter->record(FrameType::Ping->value);

        usleep(2000);

        $limiter->record(FrameType::Ping->value);

        static::assertTrue(true);
    }

    public function testExceedMessageContainsFrameTypeName(): void
    {
        $limiter = new RateLimiter([
            FrameType::Ping->value => [1, DateTime\Duration::seconds(10)],
        ]);

        try {
            $limiter->record(FrameType::Ping->value);
            $limiter->record(FrameType::Ping->value);
            static::fail('Expected ProtocolException');
        } catch (ProtocolException $e) {
            static::assertStringContainsString('Ping', $e->getMessage());
            static::assertStringContainsString('Rate limit exceeded for frame type', $e->getMessage());
        }
    }
}
