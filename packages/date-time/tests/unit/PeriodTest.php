<?php

declare(strict_types=1);

namespace Psl\DateTime\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\DateTime;
use Psl\Json;

use function serialize;
use function unserialize;

final class PeriodTest extends TestCase
{
    use DateTimeTestTrait;

    public function testGetters(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);

        static::assertSame(1, $p->getYears());
        static::assertSame(2, $p->getMonths());
        static::assertSame(3, $p->getDays());
        static::assertSame([1, 2, 3], $p->getParts());
    }

    public function testNamedConstructors(): void
    {
        static::assertSame([2, 0, 0], DateTime\Period::years(2)->getParts());
        static::assertSame([0, 5, 0], DateTime\Period::months(5)->getParts());
        static::assertSame([0, 0, 14], DateTime\Period::weeks(2)->getParts());
        static::assertSame([0, 0, -7], DateTime\Period::weeks(-1)->getParts());
        static::assertSame([0, 0, 10], DateTime\Period::days(10)->getParts());
        static::assertSame([0, 0, 0], DateTime\Period::zero()->getParts());
    }

    public function testMonthsOverflowIntoYears(): void
    {
        static::assertSame([1, 2, 0], DateTime\Period::months(14)->getParts());
        static::assertSame([2, 0, 0], DateTime\Period::months(24)->getParts());
        static::assertSame([-1, -2, 0], DateTime\Period::months(-14)->getParts());
        static::assertSame([-2, 0, 0], DateTime\Period::months(-24)->getParts());
    }

    public function testDaysNotNormalized(): void
    {
        // Days should never be normalized into months
        static::assertSame([0, 0, 365], DateTime\Period::days(365)->getParts());
        static::assertSame([0, 0, 31], DateTime\Period::days(31)->getParts());
        static::assertSame([0, 0, -100], DateTime\Period::days(-100)->getParts());
    }

    /**
     * @return list<array{int, int, int, array{int, int, int}}>
     */
    public static function provideNormalized(): array
    {
        return [
            [0, 0, 0, [0, 0, 0]],
            [1, 0, 0, [1, 0, 0]],
            [0, 14, 0, [1, 2, 0]],
            [0, -14, 0, [-1, -2, 0]],
            [1, -14, 0, [0, -2, 0]],
            [2, -14, 0, [0, 10, 0]],
            [1, 0, 5, [1, 0, 5]],
            [0, 0, 100, [0, 0, 100]],
            [-1, 14, 0, [0, 2, 0]],
            [-1, 0, -5, [-1, 0, -5]],
        ];
    }

    #[DataProvider('provideNormalized')]
    public function testNormalization(int $years, int $months, int $days, array $expected): void
    {
        static::assertSame($expected, DateTime\Period::fromParts($years, $months, $days)->getParts());
    }

    public function testSignCoherence(): void
    {
        // When years and months have the same sign, they stay coherent
        $p = DateTime\Period::fromParts(1, -3);
        static::assertSame([0, 9, 0], $p->getParts());

        $p = DateTime\Period::fromParts(-1, 3);
        static::assertSame([0, -9, 0], $p->getParts());
    }

    /**
     * @return list<array{int, int, int, int}>
     */
    public static function providePositiveNegative(): array
    {
        return [
            // y, m, d, expected sign
            [0, 0, 0, 0],
            [1, 0, 0, 1],
            [0, 1, 0, 1],
            [0, 0, 1, 1],
            [-1, 0, 0, -1],
            [0, -1, 0, -1],
            [0, 0, -1, -1],
        ];
    }

    #[DataProvider('providePositiveNegative')]
    public function testPositiveNegative(int $y, int $m, int $d, int $expectedSign): void
    {
        $p = DateTime\Period::fromParts($y, $m, $d);
        static::assertSame(0 === $expectedSign, $p->isZero());
        static::assertSame(1 === $expectedSign, $p->isPositive());
        static::assertSame(-1 === $expectedSign, $p->isNegative());
    }

    public function testWithers(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);

        static::assertSame([5, 2, 3], $p->withYears(5)->getParts());
        static::assertSame([1, 5, 3], $p->withMonths(5)->getParts());
        static::assertSame([1, 2, 5], $p->withDays(5)->getParts());

        // Normalization applies
        static::assertSame([2, 3, 3], $p->withMonths(15)->getParts());

        // Original unchanged
        static::assertSame([1, 2, 3], $p->getParts());
    }

    public function testEquals(): void
    {
        $a = DateTime\Period::fromParts(1, 2, 3);
        $b = DateTime\Period::fromParts(1, 2, 3);
        $c = DateTime\Period::fromParts(1, 2, 4);

        static::assertTrue($a->equals($b));
        static::assertFalse($a->equals($c));
    }

    public function testEqualsAfterNormalization(): void
    {
        $a = DateTime\Period::fromParts(1, 2, 0);
        $b = DateTime\Period::months(14);

        static::assertTrue($a->equals($b));
    }

    public function testInvert(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);
        static::assertSame([-1, -2, -3], $p->invert()->getParts());

        $z = DateTime\Period::zero();
        static::assertSame($z, $z->invert());
    }

    public function testPlus(): void
    {
        $a = DateTime\Period::fromParts(1, 2, 3);
        $b = DateTime\Period::fromParts(2, 3, 4);

        static::assertSame([3, 5, 7], $a->plus($b)->getParts());
    }

    public function testPlusWithOverflow(): void
    {
        $a = DateTime\Period::fromParts(1, 10, 0);
        $b = DateTime\Period::fromParts(0, 5, 0);

        static::assertSame([2, 3, 0], $a->plus($b)->getParts());
    }

    public function testPlusZeroReturnsSameInstance(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);
        $z = DateTime\Period::zero();

        static::assertSame($p, $p->plus($z));
        static::assertSame($p, $z->plus($p));
    }

    public function testMinus(): void
    {
        $a = DateTime\Period::fromParts(3, 5, 7);
        $b = DateTime\Period::fromParts(1, 2, 3);

        static::assertSame([2, 3, 4], $a->minus($b)->getParts());
    }

    public function testMinusZeroReturnsSameInstance(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);
        $z = DateTime\Period::zero();

        static::assertSame($p, $p->minus($z));
    }

    public function testZeroMinusOtherReturnsInverted(): void
    {
        $z = DateTime\Period::zero();
        $p = DateTime\Period::fromParts(1, 2, 3);

        $result = $z->minus($p);

        static::assertSame([-1, -2, -3], $result->getParts());
        static::assertTrue($result->equals($p->invert()));
    }

    /**
     * @return list<array{int, int, int, string}>
     */
    public static function provideToString(): array
    {
        return [
            [1, 0, 0, '1 year(s)'],
            [0, 5, 0, '5 month(s)'],
            [0, 0, 10, '10 day(s)'],
            [0, 0, 0, '0 day(s)'],
            [1, 2, 0, '1 year(s), 2 month(s)'],
            [1, 0, 3, '1 year(s), 0 month(s), 3 day(s)'],
            [0, 2, 3, '2 month(s), 3 day(s)'],
            [1, 2, 3, '1 year(s), 2 month(s), 3 day(s)'],
            [-1, -2, -3, '-1 year(s), -2 month(s), -3 day(s)'],
        ];
    }

    #[DataProvider('provideToString')]
    public function testToString(int $y, int $m, int $d, string $expected): void
    {
        static::assertSame($expected, DateTime\Period::fromParts($y, $m, $d)->toString());
    }

    public function testMagicToStringMatchesToString(): void
    {
        $p = DateTime\Period::fromParts(1, 2, 3);

        static::assertSame($p->toString(), (string) $p);
    }

    public function testSerialization(): void
    {
        $period = DateTime\Period::fromParts(1, 6, 15);
        $serialized = serialize($period);
        $deserialized = unserialize($serialized);

        static::assertEquals($period, $deserialized);
    }

    public function testJsonEncoding(): void
    {
        $period = DateTime\Period::fromParts(1, 6, 15);
        $jsonEncoded = Json\encode($period);
        $jsonDecoded = Json\decode($jsonEncoded);

        static::assertSame(['years' => 1, 'months' => 6, 'days' => 15], $jsonDecoded);
    }

    /**
     * @return list<array{int, int, int, string}>
     */
    public static function provideToIso8601(): array
    {
        return [
            [0, 0, 0, 'P0D'],
            [1, 0, 0, 'P1Y'],
            [0, 6, 0, 'P6M'],
            [0, 0, 15, 'P15D'],
            [1, 6, 15, 'P1Y6M15D'],
            [2, 0, 3, 'P2Y3D'],
            [0, 3, 10, 'P3M10D'],
            [-1, 0, 0, '-P1Y'],
            [-1, -6, -15, '-P1Y6M15D'],
            [0, 0, -5, '-P5D'],
            [0, -3, 0, '-P3M'],
        ];
    }

    #[DataProvider('provideToIso8601')]
    public function testToIso8601(int $y, int $m, int $d, string $expected): void
    {
        static::assertSame($expected, DateTime\Period::fromParts($y, $m, $d)->toIso8601());
    }

    public function testToIso8601ThrowsOnMixedSigns(): void
    {
        $p = DateTime\Period::fromParts(1, 0, -5);

        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot represent a Period with mixed-sign components in ISO 8601 format.');

        $p->toIso8601();
    }

    /**
     * @return list<array{string, array{int, int, int}}>
     */
    public static function provideFromIso8601(): array
    {
        return [
            ['P0D', [0, 0, 0]],
            ['P1Y', [1, 0, 0]],
            ['P6M', [0, 6, 0]],
            ['P15D', [0, 0, 15]],
            ['P1Y6M15D', [1, 6, 15]],
            ['P2Y3D', [2, 0, 3]],
            ['P3M10D', [0, 3, 10]],
            ['-P1Y', [-1, 0, 0]],
            ['-P1Y6M15D', [-1, -6, -15]],
            ['-P5D', [0, 0, -5]],
            ['P2W', [0, 0, 14]],
            ['-P3W', [0, 0, -21]],
            ['P14M', [1, 2, 0]],
        ];
    }

    #[DataProvider('provideFromIso8601')]
    public function testFromIso8601(string $iso, array $expectedParts): void
    {
        static::assertSame($expectedParts, DateTime\Period::fromIso8601($iso)->getParts());
    }

    public function testFromIso8601RoundTrip(): void
    {
        $p = DateTime\Period::fromParts(1, 6, 15);

        static::assertTrue($p->equals(DateTime\Period::fromIso8601($p->toIso8601())));
    }

    public function testFromIso8601EmptyString(): void
    {
        $this->expectException(DateTime\Exception\ParserException::class);
        $this->expectExceptionMessage('Invalid ISO 8601 period "".');

        DateTime\Period::fromIso8601('');
    }

    public function testFromIso8601MissingP(): void
    {
        $this->expectException(DateTime\Exception\ParserException::class);
        $this->expectExceptionMessage('Invalid ISO 8601 period "1Y2M".');

        DateTime\Period::fromIso8601('1Y2M');
    }

    public function testFromIso8601RejectsTimeComponent(): void
    {
        $this->expectException(DateTime\Exception\ParserException::class);
        $this->expectExceptionMessage('contains time components; use Duration::fromIso8601() instead.');

        DateTime\Period::fromIso8601('P1YT5H');
    }

    public function testFromIso8601InvalidFormat(): void
    {
        $this->expectException(DateTime\Exception\ParserException::class);
        $this->expectExceptionMessage('Invalid ISO 8601 period "PABC".');

        DateTime\Period::fromIso8601('PABC');
    }

    public function testAddToDateTime(): void
    {
        $dt = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 1, 15, 10, 30, 0);
        $period = DateTime\Period::fromParts(1, 2, 3);

        $result = $period->addTo($dt);

        static::assertInstanceOf(DateTime\DateTimeInterface::class, $result);
        /** @var DateTime\DateTimeInterface $result */
        static::assertSame(2026, $result->getYear());
        static::assertSame(3, $result->getMonth());
        static::assertSame(18, $result->getDay());
        static::assertSame(10, $result->getHours());
        static::assertSame(30, $result->getMinutes());
    }

    public function testSubtractFromDateTime(): void
    {
        $dt = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 6, 15, 10, 0, 0);
        $period = DateTime\Period::months(2);

        $result = $period->subtractFrom($dt);

        static::assertInstanceOf(DateTime\DateTimeInterface::class, $result);
        /** @var DateTime\DateTimeInterface $result */
        static::assertSame(4, $result->getMonth());
        static::assertSame(15, $result->getDay());
    }

    public function testAddToTimestampThrows(): void
    {
        $ts = DateTime\Timestamp::fromParts(1000, 0);
        $period = DateTime\Period::months(1);

        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add a Period to a Timestamp');

        $period->addTo($ts);
    }

    public function testSubtractFromTimestampThrows(): void
    {
        $ts = DateTime\Timestamp::fromParts(1000, 0);
        $period = DateTime\Period::months(1);

        $this->expectException(DateTime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot subtract a Period from a Timestamp');

        $period->subtractFrom($ts);
    }

    public function testBetweenSameDate(): void
    {
        $dt = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 3, 15);
        $period = DateTime\Period::between($dt, $dt);

        static::assertSame(0, $period->getYears());
        static::assertSame(0, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testBetweenDifferentDates(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 1, 15);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 20);
        $period = DateTime\Period::between($start, $end);

        static::assertSame(1, $period->getYears());
        static::assertSame(2, $period->getMonths());
        static::assertSame(5, $period->getDays());
    }

    public function testBetweenNegative(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 20);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 1, 15);
        $period = DateTime\Period::between($start, $end);

        static::assertTrue($period->isNegative());
    }

    public function testBetweenMonthEndClamping(): void
    {
        // Jan 31 to Feb 28 = 0 years, 0 months, 28 days
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 1, 31);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 2, 29);
        $period = DateTime\Period::between($start, $end);

        static::assertSame(0, $period->getYears());
        static::assertSame(0, $period->getMonths());
        static::assertSame(29, $period->getDays());
    }

    public function testBetweenOneMonth(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 1, 15);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 2, 15);
        $period = DateTime\Period::between($start, $end);

        static::assertSame(0, $period->getYears());
        static::assertSame(1, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testToStdlibPositive(): void
    {
        $period = DateTime\Period::fromParts(1, 2, 3);
        $interval = $period->toStdlib();

        static::assertSame(1, $interval->y);
        static::assertSame(2, $interval->m);
        static::assertSame(3, $interval->d);
        static::assertSame(0, $interval->invert);
    }

    public function testToStdlibNegative(): void
    {
        $period = DateTime\Period::fromParts(-1, -2, -3);
        $interval = $period->toStdlib();

        static::assertSame(-1, $interval->y);
        static::assertSame(-2, $interval->m);
        static::assertSame(-3, $interval->d);
    }

    public function testToStdlibZero(): void
    {
        $period = DateTime\Period::zero();
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(0, $interval->m);
        static::assertSame(0, $interval->d);
    }

    public function testToStdlibYearsOnly(): void
    {
        $period = DateTime\Period::years(3);
        $interval = $period->toStdlib();

        static::assertSame(3, $interval->y);
        static::assertSame(0, $interval->m);
        static::assertSame(0, $interval->d);
    }

    public function testToStdlibDaysOnly(): void
    {
        $period = DateTime\Period::days(45);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(0, $interval->m);
        static::assertSame(45, $interval->d);
    }

    public function testToStdlibWeeks(): void
    {
        $period = DateTime\Period::weeks(2);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(0, $interval->m);
        static::assertSame(14, $interval->d);
    }

    public function testFromPartsSignCoherencePositiveYearsNegativeMonths(): void
    {
        $period = DateTime\Period::fromParts(2, -3);

        static::assertSame(1, $period->getYears());
        static::assertSame(9, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testFromPartsSignCoherenceNegativeYearsPositiveMonths(): void
    {
        $period = DateTime\Period::fromParts(-2, 3);

        static::assertSame(-1, $period->getYears());
        static::assertSame(-9, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testFromPartsDefaultMonths(): void
    {
        $period = DateTime\Period::fromParts(1);

        static::assertSame(1, $period->getYears());
        static::assertSame(0, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testBetweenCrossesJanuaryBoundary(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 11, 15);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 1, 10);
        $period = DateTime\Period::between($start, $end);

        static::assertSame(0, $period->getYears());
        static::assertSame(1, $period->getMonths());
        static::assertSame(26, $period->getDays());
    }

    public function testEqualsWithSameYearsButDifferentMonths(): void
    {
        $a = DateTime\Period::fromParts(1, 2, 3);
        $b = DateTime\Period::fromParts(1, 5, 3);

        static::assertFalse($a->equals($b));
    }

    public function testEqualsWithSameYearsAndMonthsButDifferentDays(): void
    {
        $a = DateTime\Period::fromParts(1, 2, 3);
        $b = DateTime\Period::fromParts(1, 2, 5);

        static::assertFalse($a->equals($b));
    }

    public function testZeroMinusPeriodReturnsInvertedPeriod(): void
    {
        $result = DateTime\Period::zero()->minus(DateTime\Period::fromParts(1, 2, 3));

        static::assertSame(-1, $result->getYears());
        static::assertSame(-2, $result->getMonths());
        static::assertSame(-3, $result->getDays());
    }

    public function testToStringZeroMonthsNonZeroDays(): void
    {
        $p = DateTime\Period::fromParts(0, 0, 5);

        static::assertSame('5 day(s)', $p->toString());
    }

    public function testToStringNonZeroMonthsZeroDays(): void
    {
        $p = DateTime\Period::fromParts(0, 3, 0);

        static::assertSame('3 month(s)', $p->toString());
    }

    public function testToStringYearsAndDaysIncludesMonths(): void
    {
        $p = DateTime\Period::fromParts(2, 0, 5);

        static::assertSame('2 year(s), 0 month(s), 5 day(s)', $p->toString());
    }

    public function testToStdlibZeroMonthsNonZeroDays(): void
    {
        $period = DateTime\Period::fromParts(0, 0, 5);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(0, $interval->m);
        static::assertSame(5, $interval->d);
    }

    public function testToStdlibNonZeroMonthsZeroDays(): void
    {
        $period = DateTime\Period::fromParts(0, 3, 0);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(3, $interval->m);
        static::assertSame(0, $interval->d);
    }

    public function testBetweenLeapYearFebruaryToMarch(): void
    {
        $start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 2, 29);
        $end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 1);
        $period = DateTime\Period::between($start, $end);

        static::assertSame(1, $period->getYears());
        static::assertSame(0, $period->getMonths());
        static::assertSame(0, $period->getDays());
    }

    public function testToStdlibNegativeOneMonth(): void
    {
        $period = DateTime\Period::fromParts(0, -1, 5);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(-1, $interval->m);
        static::assertSame(5, $interval->d);
    }

    public function testToStdlibNegativeOneDay(): void
    {
        $period = DateTime\Period::fromParts(0, 3, -1);
        $interval = $period->toStdlib();

        static::assertSame(0, $interval->y);
        static::assertSame(3, $interval->m);
        static::assertSame(-1, $interval->d);
    }
}
