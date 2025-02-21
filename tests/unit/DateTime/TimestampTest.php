<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Comparison\Order;
use Psl\DateTime\DateTime;
use Psl\DateTime\Duration;
use Psl\DateTime\Exception\OverflowException;
use Psl\DateTime\Exception\ParserException;
use Psl\DateTime\Exception\UnderflowException;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\SecondsStyle;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;
use Psl\Locale\Locale;
use Psl\Math;

use function time;

use const Psl\DateTime\NANOSECONDS_PER_SECOND;

final class TimestampTest extends TestCase
{
    use DateTimeTestTrait;

    public function testNow(): void
    {
        $timestamp = Timestamp::now();

        static::assertEqualsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function testMonotonic(): void
    {
        $timestamp = Timestamp::monotonic();

        static::assertEqualsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function testMonotonicIsPrecise(): void
    {
        $a = Timestamp::monotonic();

        Async\sleep(Duration::milliseconds(100));

        $b = Timestamp::monotonic();

        $difference = $b->since($a);

        static::assertGreaterThan(100.0, $difference->getTotalMilliseconds());
    }

    public function testSince(): void
    {
        $a = Timestamp::fromParts(20, 1);
        $b = Timestamp::fromParts(30, 2);

        $duration = $b->since($a);

        static::assertSame(10, $duration->getSeconds());
        static::assertSame(1, $duration->getNanoseconds());
    }

    public function testFromRowOverflow(): void
    {
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Adding nanoseconds would cause an overflow.');

        Timestamp::fromParts(Math\INT64_MAX, NANOSECONDS_PER_SECOND);
    }

    public function testFromRowUnderflow(): void
    {
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage('Subtracting nanoseconds would cause an underflow.');

        Timestamp::fromParts(Math\INT64_MIN, -NANOSECONDS_PER_SECOND);
    }

    public function testFromRowSimplifiesNanoseconds(): void
    {
        $timestamp = Timestamp::fromParts(0, NANOSECONDS_PER_SECOND * 20);

        static::assertSame(20, $timestamp->getSeconds());
        static::assertSame(0, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(0, 100 + (NANOSECONDS_PER_SECOND * 20));

        static::assertSame(20, $timestamp->getSeconds());
        static::assertSame(100, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(30, -NANOSECONDS_PER_SECOND * 20);

        static::assertSame(10, $timestamp->getSeconds());
        static::assertSame(0, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(10, 100 + (-NANOSECONDS_PER_SECOND * 20));

        static::assertSame(-10, $timestamp->getSeconds());
        static::assertSame(100, $timestamp->getNanoseconds());
    }

    public function testParsingFromPattern(): void
    {
        $timestamp = Timestamp::parse(raw_string: '2024 091', pattern: FormatPattern::JulianDay);

        $datetime = DateTime::fromTimestamp($timestamp, Timezone::UTC);

        static::assertSame(2024, $datetime->getYear());
        static::assertSame(3, $datetime->getMonth());
        static::assertSame(31, $datetime->getDay());
    }

    public function testFromPatternFails(): void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Unable to interpret \'2\' as a valid date/time using pattern \'yyyy DDD\'.');

        Timestamp::parse('2', pattern: FormatPattern::JulianDay);
    }

    public function testParseFormat(): void
    {
        $a = Timestamp::now();
        $string = $a->format();

        $b = Timestamp::parse($string);

        static::assertSame($a->getSeconds(), $b->getSeconds());
    }

    public function testFromStringToString(): void
    {
        $a = Timestamp::now();
        $string = $a->toString();

        $b = Timestamp::fromString($string);

        static::assertSame($a->getSeconds(), $b->getSeconds());
    }

    public function testParseFails(): void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Unable to interpret \'x\' as a valid date/time.');

        Timestamp::parse('x');
    }

    public function provideFormatParsingData(): iterable
    {
        yield [
            1711917897,
            FormatPattern::FullDateTime,
            Timezone::UTC,
            Locale::English,
            'Sunday, March 31, 2024 20:44:57',
        ];
        yield [
            1711917897,
            FormatPattern::FullDateTime,
            Timezone::AsiaShanghai,
            Locale::ChineseTraditional,
            '星期一, 4月 01, 2024 04:44:57',
        ];
        yield [
            1711917897,
            FormatPattern::Cookie,
            Timezone::AmericaNewYork,
            Locale::EnglishUnitedStates,
            'Sunday, 31-Mar-2024 16:44:57 EDT',
        ];
        yield [
            1711917897,
            FormatPattern::Http,
            Timezone::EuropeVienna,
            Locale::GermanAustria,
            'So., 31 März 2024 22:44:57 MESZ',
        ];
        yield [
            1711917897,
            FormatPattern::Email,
            Timezone::EuropeMadrid,
            Locale::SpanishSpain,
            'dom, 31 mar 2024 22:44:57 GMT+02:00',
        ];
        yield [
            1711917897,
            FormatPattern::SqlDateTime,
            Timezone::AfricaTunis,
            Locale::ArabicTunisia,
            '2024-03-31 21:44:57',
        ];
        yield [1711832400, FormatPattern::IsoOrdinalDate, Timezone::EuropeMoscow, Locale::RussianRussia, '2024-091'];
        yield [
            1711917897,
            FormatPattern::Iso8601,
            Timezone::EuropeLondon,
            Locale::EnglishUnitedKingdom,
            '2024-03-31T21:44:57.000+01:00',
        ];
    }

    /**
     * @dataProvider provideFormatParsingData
     */
    public function testFormattingAndPatternParsing(
        int $timestamp,
        string|FormatPattern $pattern,
        Timezone $timezone,
        Locale $locale,
        string $expected,
    ): void {
        $timestamp = Timestamp::fromParts($timestamp);

        $result = $timestamp->format(pattern: $pattern, timezone: $timezone, locale: $locale);

        static::assertSame($expected, $result);

        $other = Timestamp::parse($result, pattern: $pattern, timezone: $timezone, locale: $locale);

        static::assertSame($timestamp->getSeconds(), $other->getSeconds());
        static::assertSame($timestamp->getNanoseconds(), $other->getNanoseconds());
    }

    public function testToRaw(): void
    {
        $timestamp = Timestamp::fromParts(12, 10);
        $parts = $timestamp->toParts();

        static::assertSame(12, $parts[0]);
        static::assertSame(10, $parts[1]);
    }

    /**
     * @return list<array{Timestamp, Timestamp, Order}>
     */
    public static function provideCompare(): array
    {
        return [
            [Timestamp::fromParts(100), Timestamp::fromParts(42), Order::Greater],
            [Timestamp::fromParts(42), Timestamp::fromParts(42), Order::Equal],
            [Timestamp::fromParts(42), Timestamp::fromParts(100), Order::Less],
            // Nanoseconds
            [Timestamp::fromParts(42, 100), Timestamp::fromParts(42, 42), Order::Greater],
            [Timestamp::fromParts(42, 42), Timestamp::fromParts(42, 42), Order::Equal],
            [Timestamp::fromParts(42, 42), Timestamp::fromParts(42, 100), Order::Less],
        ];
    }
    /**
     * @dataProvider provideCompare
     */
    public function testCompare(Timestamp $a, Timestamp $b, Order $expected): void
    {
        $opposite = Order::from(-$expected->value);

        static::assertSame($expected, $a->compare($b));
        static::assertSame($opposite, $b->compare($a));
        static::assertSame($expected === Order::Equal, $a->equals($b));
        static::assertSame($expected === Order::Less, $a->before($b));
        static::assertSame($expected !== Order::Greater, $a->beforeOrAtTheSameTime($b));
        static::assertSame($expected === Order::Greater, $a->after($b));
        static::assertSame($expected !== Order::Less, $a->afterOrAtTheSameTime($b));
        static::assertFalse($a->betweenTimeExclusive($a, $a));
        static::assertFalse($a->betweenTimeExclusive($a, $b));
        static::assertFalse($a->betweenTimeExclusive($b, $a));
        static::assertFalse($a->betweenTimeExclusive($b, $b));
        static::assertTrue($a->betweenTimeInclusive($a, $a));
        static::assertTrue($a->betweenTimeInclusive($a, $b));
        static::assertTrue($a->betweenTimeInclusive($b, $a));
        static::assertSame($expected === Order::Equal, $a->betweenTimeInclusive($b, $b));
    }

    public function testNanosecondsModifications(): void
    {
        $timestamp = Timestamp::fromParts(0, 100);

        static::assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plus(Duration::nanoseconds(10));

        static::assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plus(Duration::nanoseconds(-10));

        static::assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minus(Duration::nanoseconds(-10));

        static::assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minus(Duration::nanoseconds(10));

        static::assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plusNanoseconds(10);

        static::assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plusNanoseconds(-10);

        static::assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minusNanoseconds(-10);

        static::assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minusNanoseconds(10);

        static::assertSame(100, $timestamp->getNanoseconds());
    }

    public function testSecondsModifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::seconds(1));

        static::assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::seconds(-1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::seconds(-1));

        static::assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::seconds(1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusSeconds(1);

        static::assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->plusSeconds(-1);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusSeconds(-1);

        static::assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->minusSeconds(1);

        static::assertSame(5, $timestamp->getSeconds());
    }

    public function testMinuteModifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::minutes(1));

        static::assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::minutes(-1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::minutes(-1));

        static::assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::minutes(1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusMinutes(1);

        static::assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->plusMinutes(-1);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusMinutes(-1);

        static::assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->minusMinutes(1);

        static::assertSame(5, $timestamp->getSeconds());
    }

    public function testHourModifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::hours(1));

        static::assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::hours(-1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::hours(-1));

        static::assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::hours(1));

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusHours(1);

        static::assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->plusHours(-1);

        static::assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusHours(-1);

        static::assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->minusHours(1);

        static::assertSame(5, $timestamp->getSeconds());
    }

    public function testConvertToTimezone(): void
    {
        $timestamp = Timestamp::fromParts(1_711_917_232, 501_000_000);

        static::assertSame(
            '2024-03-31T20:33:52.501Z',
            $timestamp->convertToTimezone(Timezone::UTC)->format(pattern: FormatPattern::Iso8601),
        );

        static::assertSame(
            '2024-03-31T21:33:52.501+01:00',
            $timestamp->convertToTimezone(Timezone::AfricaTunis)->format(pattern: FormatPattern::Iso8601),
        );

        static::assertSame(
            '2024-03-31T16:33:52.501-04:00',
            $timestamp->convertToTimezone(Timezone::AmericaNewYork)->format(pattern: FormatPattern::Iso8601),
        );

        static::assertSame(
            '2024-04-01T04:33:52.501+08:00',
            $timestamp->convertToTimezone(Timezone::AsiaShanghai)->format(pattern: FormatPattern::Iso8601),
        );
    }

    public function testJsonSerialization(): void
    {
        $serialized = Timestamp::fromParts(1711917232, 12)->jsonSerialize();

        static::assertSame(1711917232, $serialized['seconds']);
        static::assertSame(12, $serialized['nanoseconds']);
    }

    public function testToRfc3999(): void
    {
        $timestamp = Timestamp::fromParts(1711917232, 12);

        static::assertSame('2024-03-31T20:33:52.12+00:00', $timestamp->toRfc3339());
        static::assertSame('2024-03-31T20:33:52+00:00', $timestamp->toRfc3339(seconds_style: SecondsStyle::Seconds));
        static::assertSame('2024-03-31T20:33:52.12Z', $timestamp->toRfc3339(use_z: true));
    }
}
