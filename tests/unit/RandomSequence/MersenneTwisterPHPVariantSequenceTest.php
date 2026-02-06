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
        yield [2_147_483_649, [90_281_504, 1_278_257_534, 1_994_752_345, 683_161_987, 992_945_549]];
        yield [45_635, [899_019_714, 822_361_780, 1_611_332_592, 632_462_060, 1_431_120_852]];
        yield [5744, [1_962_086_712, 1_462_838_808, 1_331_836_928, 446_021_369, 535_020_186]];
    }
}
