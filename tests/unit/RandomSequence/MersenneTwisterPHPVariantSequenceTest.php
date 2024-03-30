<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\RandomSequence;

use PHPUnit\Framework\TestCase;
use Psl\RandomSequence\MersenneTwisterPHPVariantSequence;

final class MersenneTwisterPHPVariantSequenceTest extends TestCase
{
    /**
     * @dataProvider provideSeeds
     */
    public function testNext(int $seed, array $expectations): void
    {
        $sequence = new MersenneTwisterPHPVariantSequence($seed);

        for ($i = 0; $i < 5; $i++) {
            static::assertSame($expectations[$i], $sequence->next());
        }
    }

    public function provideSeeds(): iterable
    {
        yield [2147483649, [90281504, 1278257534, 1994752345, 683161987, 992945549]];
        yield [45635, [899019714, 822361780, 1611332592, 632462060, 1431120852]];
        yield [5744, [1962086712, 1462838808, 1331836928, 446021369, 535020186]];
    }
}
