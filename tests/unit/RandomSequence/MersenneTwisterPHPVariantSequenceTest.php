<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\RandomSequence;

use PHPUnit\Framework\TestCase;
use Psl\RandomSequence\MersenneTwisterPHPVariantSequence;
use Psl\SecureRandom;

use function mt_rand;
use function mt_srand;
use function time;

use const MT_RAND_PHP;

final class MersenneTwisterPHPVariantSequenceTest extends TestCase
{
    /**
     * @dataProvider provideSeeds
     */
    public function testNext(int $seed): void
    {
        mt_srand($seed, MT_RAND_PHP);
        $sequence = new MersenneTwisterPHPVariantSequence($seed);

        for ($i = 0; $i < 100; $i++) {
            static::assertSame(mt_rand(), $sequence->next());
        }
    }

    public function provideSeeds(): iterable
    {
        yield [45635];
        yield [5744];
        yield [456];
        yield [34];
        yield [5];
        yield [time()];
        yield [SecureRandom\int()];
    }
}
