<?php

declare(strict_types=1);

namespace Psl\RandomSequence\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\RandomSequence\MersenneTwisterSequence;
use Psl\SecureRandom;

use function mt_rand;
use function mt_srand;
use function time;

use const MT_RAND_MT19937;

final class MersenneTwisterSequenceTest extends TestCase
{
    #[DataProvider('provideSeeds')]
    public function testNext(int $seed): void
    {
        mt_srand($seed, MT_RAND_MT19937);
        $sequence = new MersenneTwisterSequence($seed);

        for ($i = 0; $i < 100; $i++) {
            static::assertSame(mt_rand(), $sequence->next());
        }
    }

    public static function provideSeeds(): iterable
    {
        yield [2_147_483_649];
        yield [45_635];
        yield [5744];
        yield [456];
        yield [34];
        yield [5];
        yield [time()];
        yield [SecureRandom\int()];
    }
}
