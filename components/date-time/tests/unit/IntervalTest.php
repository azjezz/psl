<?php

declare(strict_types=1);

namespace Psl\DateTime\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime;
use Psl\Json;

use function serialize;
use function unserialize;

final class IntervalTest extends TestCase
{
    use DateTimeTestTrait;

    public function testBetween(): void
    {
        $start = DateTime\Timestamp::fromParts(1000, 0);
        $end = DateTime\Timestamp::fromParts(2000, 0);

        $interval = DateTime\Interval::between($start, $end);

        static::assertSame($start, $interval->getStart());
        static::assertSame($end, $interval->getEnd());
    }

    public function testBetweenSameTime(): void
    {
        $point = DateTime\Timestamp::fromParts(1000, 0);

        $interval = DateTime\Interval::between($point, $point);

        static::assertTrue($interval->getDuration()->isZero());
    }

    public function testBetweenThrowsWhenStartAfterEnd(): void
    {
        $start = DateTime\Timestamp::fromParts(2000, 0);
        $end = DateTime\Timestamp::fromParts(1000, 0);

        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Interval start must be before or at the same time as end.');

        DateTime\Interval::between($start, $end);
    }

    public function testGetDuration(): void
    {
        $start = DateTime\Timestamp::fromParts(1000, 0);
        $end = DateTime\Timestamp::fromParts(1060, 500_000_000);

        $interval = DateTime\Interval::between($start, $end);
        $duration = $interval->getDuration();

        static::assertSame(0, $duration->getHours());
        static::assertSame(1, $duration->getMinutes());
        static::assertSame(0, $duration->getSeconds());
        static::assertSame(500_000_000, $duration->getNanoseconds());
    }

    public function testContains(): void
    {
        $start = DateTime\Timestamp::fromParts(1000, 0);
        $end = DateTime\Timestamp::fromParts(2000, 0);
        $interval = DateTime\Interval::between($start, $end);

        // Point inside
        $inside = DateTime\Timestamp::fromParts(1500, 0);
        static::assertTrue($interval->contains($inside));

        // Point on start boundary
        static::assertTrue($interval->contains($start));

        // Point on end boundary
        static::assertTrue($interval->contains($end));

        // Point before
        $before = DateTime\Timestamp::fromParts(999, 0);
        static::assertFalse($interval->contains($before));

        // Point after
        $after = DateTime\Timestamp::fromParts(2001, 0);
        static::assertFalse($interval->contains($after));
    }

    public function testOverlaps(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(2000, 0));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(1500, 0), DateTime\Timestamp::fromParts(2500, 0));

        static::assertTrue($a->overlaps($b));
        static::assertTrue($b->overlaps($a));
    }

    public function testOverlapsAtBoundary(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(2000, 0));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(2000, 0), DateTime\Timestamp::fromParts(3000, 0));

        // Touching at boundary counts as overlap
        static::assertTrue($a->overlaps($b));
        static::assertTrue($b->overlaps($a));
    }

    public function testDoesNotOverlap(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(2000, 0));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(2001, 0), DateTime\Timestamp::fromParts(3000, 0));

        static::assertFalse($a->overlaps($b));
        static::assertFalse($b->overlaps($a));
    }

    public function testOverlapsContained(): void
    {
        $outer = DateTime\Interval::between(
            DateTime\Timestamp::fromParts(1000, 0),
            DateTime\Timestamp::fromParts(3000, 0),
        );
        $inner = DateTime\Interval::between(
            DateTime\Timestamp::fromParts(1500, 0),
            DateTime\Timestamp::fromParts(2500, 0),
        );

        static::assertTrue($outer->overlaps($inner));
        static::assertTrue($inner->overlaps($outer));
    }

    public function testEquals(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(2000, 0));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(2000, 0));
        $c = DateTime\Interval::between(DateTime\Timestamp::fromParts(1000, 0), DateTime\Timestamp::fromParts(3000, 0));

        static::assertTrue($a->equals($b));
        static::assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $start = DateTime\Timestamp::fromParts(0, 0);
        $end = DateTime\Timestamp::fromParts(86_400, 0);

        $interval = DateTime\Interval::between($start, $end);
        $str = $interval->toString();

        static::assertSame($start->toRfc3339() . ' / ' . $end->toRfc3339(), $str);
    }

    public function testMagicToStringMatchesToString(): void
    {
        $interval = DateTime\Interval::between(
            DateTime\Timestamp::fromParts(1000, 0),
            DateTime\Timestamp::fromParts(2000, 0),
        );

        static::assertSame($interval->toString(), (string) $interval);
    }

    public function testSerialization(): void
    {
        $interval = DateTime\Interval::between(
            DateTime\Timestamp::fromParts(1000, 0),
            DateTime\Timestamp::fromParts(2000, 0),
        );
        $serialized = serialize($interval);
        $deserialized = unserialize($serialized);

        static::assertEquals($interval, $deserialized);
    }

    public function testJsonEncoding(): void
    {
        $start = DateTime\Timestamp::fromParts(1000, 0);
        $end = DateTime\Timestamp::fromParts(2000, 0);
        $interval = DateTime\Interval::between($start, $end);

        $jsonEncoded = Json\encode($interval);
        $jsonDecoded = Json\decode($jsonEncoded);

        static::assertSame(
            [
                'start' => $start->jsonSerialize(),
                'end' => $end->jsonSerialize(),
            ],
            $jsonDecoded,
        );
    }

    public function testContainsWithDateTime(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 1, 1, 0, 0, 0);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 12, 31, 23, 59, 59);
        $interval = DateTime\Interval::between($start, $end);

        $mid = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 6, 15, 12, 0, 0);

        static::assertTrue($interval->contains($mid));
    }

    public function testFrom(): void
    {
        $start = DateTime\Timestamp::fromParts(1000);
        $duration = DateTime\Duration::seconds(500);
        $interval = DateTime\Interval::from($start, $duration);

        static::assertSame(1000, $interval->getStart()->getTimestamp()->getSeconds());
        static::assertSame(1500, $interval->getEnd()->getTimestamp()->getSeconds());
    }

    public function testFromRejectsNegativeDuration(): void
    {
        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Interval duration must not be negative.');

        DateTime\Interval::from(DateTime\Timestamp::fromParts(1000), DateTime\Duration::seconds(-1));
    }

    public function testContainsInterval(): void
    {
        $outer = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(500));
        $inner = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(400));
        $partial = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(600));

        static::assertTrue($outer->containsInterval($inner));
        static::assertTrue($outer->containsInterval($outer));
        static::assertFalse($outer->containsInterval($partial));
        static::assertFalse($inner->containsInterval($outer));
    }

    public function testIntersection(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(400));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(600));

        $intersection = $a->intersection($b);

        static::assertNotNull($intersection);
        static::assertSame(300, $intersection->getStart()->getTimestamp()->getSeconds());
        static::assertSame(400, $intersection->getEnd()->getTimestamp()->getSeconds());
    }

    public function testIntersectionReturnsNullWhenNoOverlap(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(400));

        static::assertNull($a->intersection($b));
    }

    public function testIntersectionFullyContained(): void
    {
        $outer = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(500));
        $inner = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(300));

        $intersection = $outer->intersection($inner);

        static::assertNotNull($intersection);
        static::assertSame(200, $intersection->getStart()->getTimestamp()->getSeconds());
        static::assertSame(300, $intersection->getEnd()->getTimestamp()->getSeconds());
    }

    public function testGapBetweenNonOverlapping(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(400));

        $gap = $a->gap($b);

        static::assertNotNull($gap);
        static::assertSame(200, $gap->getStart()->getTimestamp()->getSeconds());
        static::assertSame(300, $gap->getEnd()->getTimestamp()->getSeconds());
    }

    public function testGapReversed(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(400));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));

        $gap = $a->gap($b);

        static::assertNotNull($gap);
        static::assertSame(200, $gap->getStart()->getTimestamp()->getSeconds());
        static::assertSame(300, $gap->getEnd()->getTimestamp()->getSeconds());
    }

    public function testGapReturnsNullWhenOverlapping(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(300));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(400));

        static::assertNull($a->gap($b));
    }

    public function testGapReturnsNullWhenAdjacent(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(300));

        // Adjacent intervals overlap at boundary, so no gap
        static::assertNull($a->gap($b));
    }

    public function testMergeOverlapping(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(300));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(400));

        $merged = $a->merge($b);

        static::assertSame(100, $merged->getStart()->getTimestamp()->getSeconds());
        static::assertSame(400, $merged->getEnd()->getTimestamp()->getSeconds());
    }

    public function testMergeContained(): void
    {
        $outer = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(500));
        $inner = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(300));

        $merged = $outer->merge($inner);

        static::assertSame(100, $merged->getStart()->getTimestamp()->getSeconds());
        static::assertSame(500, $merged->getEnd()->getTimestamp()->getSeconds());
    }

    public function testMergeAdjacent(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(200), DateTime\Timestamp::fromParts(300));

        $merged = $a->merge($b);

        static::assertSame(100, $merged->getStart()->getTimestamp()->getSeconds());
        static::assertSame(300, $merged->getEnd()->getTimestamp()->getSeconds());
    }

    public function testMergeThrowsWhenNonOverlapping(): void
    {
        $a = DateTime\Interval::between(DateTime\Timestamp::fromParts(100), DateTime\Timestamp::fromParts(200));
        $b = DateTime\Interval::between(DateTime\Timestamp::fromParts(300), DateTime\Timestamp::fromParts(400));

        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge non-overlapping intervals.');

        $a->merge($b);
    }

    public function testSince(): void
    {
        $start = DateTime\Timestamp::fromParts(1000);
        $interval = DateTime\Interval::since($start);

        static::assertSame(1000, $interval->getStart()->getTimestamp()->getSeconds());
        static::assertTrue($interval->getEnd()->afterOrAtTheSameTime($interval->getStart()));
    }

    public function testToIso8601(): void
    {
        $start = DateTime\Timestamp::fromParts(1_711_917_232, 0);
        $end = DateTime\Timestamp::fromParts(1_711_917_300, 500_000_000);
        $interval = DateTime\Interval::between($start, $end);

        static::assertSame('2024-03-31T20:33:52Z/2024-03-31T20:35:00.500Z', $interval->toIso8601());
    }

    public function testFromIso8601(): void
    {
        $interval = DateTime\Interval::fromIso8601('2024-03-31T20:33:52Z/2024-03-31T20:35:00Z');

        static::assertSame(1_711_917_232, $interval->getStart()->getTimestamp()->getSeconds());
        static::assertSame(1_711_917_300, $interval->getEnd()->getTimestamp()->getSeconds());
    }

    public function testIso8601RoundTrip(): void
    {
        $start = DateTime\Timestamp::fromParts(1_711_917_232, 0);
        $end = DateTime\Timestamp::fromParts(1_711_917_300, 0);
        $interval = DateTime\Interval::between($start, $end);

        $iso = $interval->toIso8601();
        $parsed = DateTime\Interval::fromIso8601($iso);

        static::assertTrue($interval->equals($parsed));
    }

    public function testFromIso8601InvalidFormat(): void
    {
        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISO 8601 interval format; expected "start/end".');

        DateTime\Interval::fromIso8601('invalid');
    }

    public function testFromIso8601InvalidTimestamp(): void
    {
        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISO 8601 interval format; Failed to parse time string');

        DateTime\Interval::fromIso8601('not-a-date/also-not-a-date');
    }
}
