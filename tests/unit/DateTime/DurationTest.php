<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\Comparison\Order;
use Psl\DateTime;
use Psl\Json;

use function serialize;
use function unserialize;

/**
 * @mago-ignore php-unit/strict-assertions
 */
final class DurationTest extends TestCase
{
    use DateTimeTestTrait;

    public function testGetters(): void
    {
        $t = DateTime\Duration::fromParts(1, 2, 3, 4);

        static::assertSame(1, $t->getHours());
        static::assertSame(2, $t->getMinutes());
        static::assertSame(3, $t->getSeconds());
        static::assertSame(4, $t->getNanoseconds());
        static::assertSame([1, 2, 3, 4], $t->getParts());
    }

    public function testNamedConstructors(): void
    {
        static::assertSame(168.0, DateTime\Duration::weeks(1)->getTotalHours());
        static::assertSame(24.0, DateTime\Duration::days(1)->getTotalHours());
        static::assertSame(1.0, DateTime\Duration::hours(1)->getTotalHours());
        static::assertSame(1.0, DateTime\Duration::minutes(1)->getTotalMinutes());
        static::assertSame(1.0, DateTime\Duration::seconds(1)->getTotalSeconds());
        static::assertSame(1.0, DateTime\Duration::milliseconds(1)->getTotalMilliseconds());
        static::assertSame(1.0, DateTime\Duration::microseconds(1)->getTotalMicroseconds());
        static::assertSame(1, DateTime\Duration::nanoseconds(1)->getNanoseconds());
        static::assertSame(0.0, DateTime\Duration::zero(1)->getTotalSeconds());
    }

    public function provideGetTotalHours(): array
    {
        return [
            [0, 0, 0, 0, 0.0],
            [0, 0, 0, 1, 2.777777777777778E-13],
            [1, 0, 0, 0, 1.0],
            [1, 30, 0, 0, 1.5],
            [2, 15, 30, 0, 2.2583333333333333],
            [-1, 0, 0, 0, -1.0],
            [-1, -30, 0, 0, -1.5],
            [-2, -15, -30, 0, -2.2583333333333333],
        ];
    }

    /**
     * @dataProvider provideGetTotalHours
     */
    public function testGetTotalHours(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedHours,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        static::assertSame($expectedHours, $time->getTotalHours());
    }

    public function provideGetTotalMinutes(): array
    {
        return [
            [0, 0, 0, 0, 0.0],
            [0, 0, 0, 1, 1.6666666666666667E-11],
            [1, 0, 0, 0, 60.0],
            [1, 30, 0, 0, 90.0],
            [2, 15, 30, 0, 135.5],
            [-1, 0, 0, 0, -60.0],
            [-1, -30, 0, 0, -90.0],
            [-2, -15, -30, 0, -135.5],
        ];
    }

    /**
     * @dataProvider provideGetTotalMinutes
     */
    public function testGetTotalMinutes(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedMinutes,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        static::assertSame($expectedMinutes, $time->getTotalMinutes());
    }

    public function provideGetTotalSeconds(): array
    {
        return [
            [0, 0, 0, 0, 0.0],
            [0, 0, 0, 1, 0.000000001],
            [1, 0, 0, 0, 3600.0],
            [1, 30, 0, 0, 5400.0],
            [2, 15, 30, 0, 8130.0],
            [-1, 0, 0, 0, -3600.0],
            [-1, -30, 0, 0, -5400.0],
            [-2, -15, -30, 0, -8130.0],
        ];
    }

    /**
     * @dataProvider provideGetTotalSeconds
     */
    public function testGetTotalSeconds(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedSeconds,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        static::assertSame($expectedSeconds, $time->getTotalSeconds());
    }

    public function provideGetTotalMilliseconds(): array
    {
        return [
            [0, 0, 0, 0, 0.0],
            [0, 0, 0, 1, 0.000001],
            [1, 0, 0, 0, 3600000.0],
            [1, 30, 0, 0, 5400000.0],
            [2, 15, 30, 0, 8130000.0],
            [-1, 0, 0, 0, -3600000.0],
            [-1, -30, 0, 0, -5400000.0],
            [-2, -15, -30, 0, -8130000.0],
        ];
    }

    /**
     * @dataProvider provideGetTotalMilliseconds
     */
    public function testGetTotalMilliseconds(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedMilliseconds,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        static::assertSame($expectedMilliseconds, $time->getTotalMilliseconds());
    }

    public function provideGetTotalMicroseconds(): array
    {
        return [
            [0, 0, 0, 0, 0.0],
            [0, 0, 0, 1, 0.001],
            [1, 0, 0, 0, 3600000000.0],
            [1, 30, 0, 0, 5400000000.0],
            [2, 15, 30, 0, 8130000000.0],
            [-1, 0, 0, 0, -3600000000.0],
            [-1, -30, 0, 0, -5400000000.0],
            [-2, -15, -30, 0, -8130000000.0],
        ];
    }

    /**
     * @dataProvider provideGetTotalMicroseconds
     */
    public function testGetTotalMicroseconds(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedMicroseconds,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        static::assertSame($expectedMicroseconds, $time->getTotalMicroseconds());
    }

    public function testSetters(): void
    {
        $t = DateTime\Duration::fromParts(1, 2, 3, 4);

        static::assertSame([42, 2, 3, 4], $t->withHours(42)->getParts());
        static::assertSame([1, 42, 3, 4], $t->withMinutes(42)->getParts());
        static::assertSame([1, 2, 42, 4], $t->withSeconds(42)->getParts());
        static::assertSame([1, 2, 3, 42], $t->withNanoseconds(42)->getParts());
        static::assertSame([2, 3, 3, 4], $t->withMinutes(63)->getParts());
        static::assertSame([1, 3, 3, 4], $t->withSeconds(63)->getParts());
        static::assertSame([1, 2, 4, 42], $t->withNanoseconds(DateTime\NANOSECONDS_PER_SECOND + 42)->getParts());
        static::assertSame([1, 2, 3, 4], $t->getParts());
    }
    public function testFractionsOfSecond(): void
    {
        static::assertSame([0, 0, 0, 0], DateTime\Duration::zero()->getParts());
        static::assertSame([0, 0, 0, 42], DateTime\Duration::nanoseconds(42)->getParts());
        static::assertSame(
            [0, 0, 1, 42],
            DateTime\Duration::nanoseconds(DateTime\NANOSECONDS_PER_SECOND + 42)->getParts(),
        );
        static::assertSame([0, 0, 0, 42000], DateTime\Duration::microseconds(42)->getParts());
        static::assertSame([0, 0, 1, 42000], DateTime\Duration::microseconds(1000042)->getParts());
        static::assertSame([0, 0, 0, 42000000], DateTime\Duration::milliseconds(42)->getParts());
        static::assertSame([0, 0, 1, 42000000], DateTime\Duration::milliseconds(1042)->getParts());
    }

    /**
     * @return list<array{int, int, int, int}>
     */
    public static function provideNormalized(): array
    {
        return [
            // input seconds, input ns, normalized seconds, normalized ns
            [0, 0, 0, 0],
            [0, 3, 0, 3],
            [3, 0, 3, 0],
            [1, 3, 1, 3],
            [1, -3, 0, DateTime\NANOSECONDS_PER_SECOND - 3],
            [-1, 3, 0, -(DateTime\NANOSECONDS_PER_SECOND - 3)],
            [-1, -3, -1, -3],
            [1, DateTime\NANOSECONDS_PER_SECOND + 42, 2, 42],
            [1, -(DateTime\NANOSECONDS_PER_SECOND + 42), 0, -42],
            [2, -3, 1, DateTime\NANOSECONDS_PER_SECOND - 3],
        ];
    }
    /**
     * @dataProvider provideNormalized
     */
    public function testNormalized(int $input_s, int $input_ns, int $normalized_s, int $normalized_ns): void
    {
        static::assertSame(
            [0, 0, $normalized_s, $normalized_ns],
            DateTime\Duration::fromParts(0, 0, $input_s, $input_ns)->getParts(),
        );
    }

    public function testNormalizedHMS(): void
    {
        static::assertSame([3, 5, 4, 0], DateTime\Duration::fromParts(2, 63, 124)->getParts());
        static::assertSame([0, 59, 4, 0], DateTime\Duration::fromParts(2, -63, 124)->getParts());
        static::assertSame(
            [-1, 0, -55, -(DateTime\NANOSECONDS_PER_SECOND - 42)],
            DateTime\Duration::fromParts(0, -63, 124, 42)->getParts(),
        );
        static::assertSame([42, 0, 0, 0], DateTime\Duration::hours(42)->getParts());
        static::assertSame([1, 3, 0, 0], DateTime\Duration::minutes(63)->getParts());
        static::assertSame([0, -1, -3, 0], DateTime\Duration::seconds(-63)->getParts());
        static::assertSame([0, 0, -1, 0], DateTime\Duration::nanoseconds(-DateTime\NANOSECONDS_PER_SECOND)->getParts());
    }

    /**
     * @return list<array{int, int, int, int, int}>
     */
    public static function providePositiveNegative(): array
    {
        return [
            // h, m, s, ns, expected sign
            [0, 0, 0, 0, 0],
            [0, 42, 0, 0, 1],
            [0, 0, -42, 0, -1],
            [1, -63, 0, 0, -1],
        ];
    }
    /**
     * @dataProvider providePositiveNegative
     */
    public function testPositiveNegative(int $h, int $m, int $s, int $ns, int $expected_sign): void
    {
        $t = DateTime\Duration::fromParts($h, $m, $s, $ns);
        static::assertSame($expected_sign === 0, $t->isZero());
        static::assertSame($expected_sign === 1, $t->isPositive());
        static::assertSame($expected_sign === -1, $t->isNegative());
    }

    /**
     * @return list<array{DateTime\Duration, DateTime\Duration, Order}>
     */
    public static function provideCompare(): array
    {
        return [
            [DateTime\Duration::seconds(20), DateTime\Duration::seconds(10), Order::Greater],
            [DateTime\Duration::seconds(10), DateTime\Duration::seconds(20), Order::Less],
            [DateTime\Duration::seconds(10), DateTime\Duration::seconds(10), Order::Equal],
            [DateTime\Duration::hours(1), DateTime\Duration::minutes(42), Order::Greater],
            [DateTime\Duration::minutes(2), DateTime\Duration::seconds(120), Order::Equal],
            [DateTime\Duration::zero(), DateTime\Duration::nanoseconds(1), Order::Less],
        ];
    }
    /**
     * @dataProvider provideCompare
     */
    public function testCompare(DateTime\Duration $a, DateTime\Duration $b, Order $expected): void
    {
        $opposite = Order::from(-$expected->value);

        static::assertSame($expected, $a->compare($b));
        static::assertSame($opposite, $b->compare($a));
        static::assertSame($expected === Order::Equal, $a->equals($b));
        static::assertSame($expected === Order::Less, $a->shorter($b));
        static::assertSame($expected !== Order::Greater, $a->shorterOrEqual($b));
        static::assertSame($expected === Order::Greater, $a->longer($b));
        static::assertSame($expected !== Order::Less, $a->longerOrEqual($b));
        static::assertFalse($a->betweenExclusive($a, $a));
        static::assertFalse($a->betweenExclusive($a, $b));
        static::assertFalse($a->betweenExclusive($b, $a));
        static::assertFalse($a->betweenExclusive($b, $b));
        static::assertTrue($a->betweenInclusive($a, $a));
        static::assertTrue($a->betweenInclusive($a, $b));
        static::assertTrue($a->betweenInclusive($b, $a));
        static::assertSame($expected === Order::Equal, $a->betweenInclusive($b, $b));
    }

    public function testIsBetween(): void
    {
        $a = DateTime\Duration::hours(1);
        $b = DateTime\Duration::minutes(64);
        $c = DateTime\Duration::fromParts(1, 30);
        static::assertTrue($b->betweenExclusive($a, $c));
        static::assertTrue($b->betweenExclusive($c, $a));
        static::assertTrue($b->betweenInclusive($a, $c));
        static::assertTrue($b->betweenInclusive($c, $a));
        static::assertFalse($a->betweenExclusive($b, $c));
        static::assertFalse($a->betweenInclusive($c, $b));
        static::assertFalse($c->betweenInclusive($a, $b));
        static::assertFalse($c->betweenExclusive($b, $a));
    }

    public function testOperations(): void
    {
        $z = DateTime\Duration::zero();
        $a = DateTime\Duration::fromParts(0, 2, 25);
        $b = DateTime\Duration::fromParts(0, 0, -63, 42);
        static::assertSame([0, 0, 0, 0], $z->invert()->getParts());
        static::assertSame([0, -2, -25, 0], $a->invert()->getParts());
        static::assertSame([0, 1, 2, DateTime\NANOSECONDS_PER_SECOND - 42], $b->invert()->getParts());
        static::assertSame($a->getParts(), $z->plus($a)->getParts());
        static::assertSame($b->getParts(), $b->plus($z)->getParts());
        static::assertSame($b->invert()->getParts(), $z->minus($b)->getParts());
        static::assertSame($a->getParts(), $a->minus($z)->getParts());
        static::assertSame([0, 1, 22, 42], $a->plus($b)->getParts());
        static::assertSame([0, 1, 22, 42], $b->plus($a)->getParts());
        static::assertSame([0, 3, 27, DateTime\NANOSECONDS_PER_SECOND - 42], $a->minus($b)->getParts());
        static::assertSame([0, -3, -27, -(DateTime\NANOSECONDS_PER_SECOND - 42)], $b->minus($a)->getParts());
        static::assertSame($b->invert()->plus($a)->getParts(), $a->minus($b)->getParts());
    }

    /**
     * @return list<array{int, int, int, int, string}>
     */
    public static function provideToString(): array
    {
        return [
            // h, m, s, ns, expected output
            [42, 0, 0, 0, '42 hour(s)'],
            [0, 42, 0, 0, '42 minute(s)'],
            [0, 0, 42, 0, '42 second(s)'],
            [0, 0, 0, 0, '0 second(s)'],
            [0, 0, 0, 42, '0 second(s)'], // rounded because default $max_decimals = 3
            [0, 0, 1, 42, '1 second(s)'],
            [0, 0, 1, 20000000, '1.02 second(s)'],
            [1, 2, 0, 0, '1 hour(s), 2 minute(s)'],
            [1, 0, 3, 0, '1 hour(s), 0 minute(s), 3 second(s)'],
            [0, 2, 3, 0, '2 minute(s), 3 second(s)'],
            [1, 2, 3, 0, '1 hour(s), 2 minute(s), 3 second(s)'],
            [1, 0, 0, 42000000, '1 hour(s), 0 minute(s), 0.042 second(s)'],
            [-42, 0, -42, 0, '-42 hour(s), 0 minute(s), -42 second(s)'],
            [-42, 0, -42, -420000000, '-42 hour(s), 0 minute(s), -42.42 second(s)'],
            [0, 0, 0, -420000000, '-0.42 second(s)'],
        ];
    }

    /**
     * @dataProvider provideToString
     */
    public function testToString(int $h, int $m, int $s, int $ns, string $expected): void
    {
        static::assertSame($expected, DateTime\Duration::fromParts($h, $m, $s, $ns)->toString());
    }

    public function testSerialization(): void
    {
        $timeInterval = DateTime\Duration::fromParts(1, 30, 45, 500000000);
        $serialized = serialize($timeInterval);
        $deserialized = unserialize($serialized);

        static::assertEquals($timeInterval, $deserialized);
    }

    public function testJsonEncoding(): void
    {
        $timeInterval = DateTime\Duration::fromParts(1, 30, 45, 500000000);
        $jsonEncoded = Json\encode($timeInterval);
        $jsonDecoded = Json\decode($jsonEncoded);

        static::assertSame(['hours' => 1, 'minutes' => 30, 'seconds' => 45, 'nanoseconds' => 500000000], $jsonDecoded);
    }
}
