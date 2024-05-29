<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Era;

final class EraTest extends TestCase
{
    use DateTimeTestTrait;

    public function provideFromYearData(): iterable
    {
        yield [2024, Era::AnnoDomini];
        yield [1, Era::AnnoDomini];
        yield [-2024, Era::BeforeChrist];
        yield [-1, Era::BeforeChrist];
        yield [0, Era::BeforeChrist];
    }

    /**
     * @dataProvider provideFromYearData
     */
    public function testFromYear(int $year, Era $expected): void
    {
        static::assertSame($expected, Era::fromYear($year));
    }

    public function testToggle(): void
    {
        static::assertSame(Era::AnnoDomini, Era::BeforeChrist->toggle());
        static::assertSame(Era::BeforeChrist, Era::AnnoDomini->toggle());
    }
}
