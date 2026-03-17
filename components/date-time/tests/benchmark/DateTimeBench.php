<?php

declare(strict_types=1);

namespace Psl\DateTime\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;

#[Groups(['datetime'])]
final class DateTimeBench
{
    /**
     * @param array{duration: Duration} $params
     */
    #[ParamProviders('provideDurationData')]
    public function benchDurationToString(array $params): void
    {
        $params['duration']->toString();
    }

    /**
     * @param array{iso: string} $params
     */
    #[ParamProviders('provideIso8601DurationData')]
    public function benchDurationFromIso8601(array $params): void
    {
        Duration::fromIso8601($params['iso']);
    }

    /**
     * @param array{duration: Duration} $params
     */
    #[ParamProviders('provideDurationData')]
    public function benchDurationToIso8601(array $params): void
    {
        $params['duration']->toIso8601();
    }

    public function benchTimestampFromParts(): void
    {
        Timestamp::fromParts(1_709_568_000, 500_000_000);
    }

    public function benchTimestampFromMilliseconds(): void
    {
        Timestamp::fromMilliseconds(1_709_568_000_500);
    }

    /**
     * @param array{timestamp: Timestamp} $params
     */
    #[ParamProviders('provideTimestampData')]
    public function benchTimestampToRfc3339(array $params): void
    {
        $params['timestamp']->toRfc3339();
    }

    /**
     * @return iterable<string, array{duration: Duration}>
     */
    public function provideDurationData(): iterable
    {
        yield 'zero' => ['duration' => Duration::zero()];
        yield 'simple_hours' => ['duration' => Duration::hours(5)];
        yield 'complex' => ['duration' => Duration::fromParts(3, 25, 45, 123_456_789)];
        yield 'negative' => ['duration' => Duration::fromParts(-2, -30, -15, -500_000_000)];
    }

    /**
     * @return iterable<string, array{iso: string}>
     */
    public function provideIso8601DurationData(): iterable
    {
        yield 'simple' => ['iso' => 'PT5H'];
        yield 'complex' => ['iso' => 'PT3H25M45.123456789S'];
        yield 'negative' => ['iso' => '-PT2H30M15.5S'];
    }

    /**
     * @return iterable<string, array{timestamp: Timestamp}>
     */
    public function provideTimestampData(): iterable
    {
        yield 'epoch' => ['timestamp' => Timestamp::fromParts(0, 0)];
        yield 'recent' => ['timestamp' => Timestamp::fromParts(1_709_568_000, 123_456_789)];
    }
}
