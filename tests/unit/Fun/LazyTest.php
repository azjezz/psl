<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Fun;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Fun;
use RuntimeException;

use function pow;

final class LazyTest extends TestCase
{
    public function testItDoesNotLoadInitializerDuringCreation(): void
    {
        $this->expectNotToPerformAssertions();

        Fun\lazy(static function () {
            throw new RuntimeException('nonono');
        });
    }

    public function testItCanBeUsedAsALazyProxy(): void
    {
        $proxy = Fun\lazy(static fn(): int => 132);

        static::assertSame(132, $proxy());
    }

    public function testItCanBeUsedAsALazyProxyAndChainCalls(): void
    {
        $x = new class {
            public function doSomething(): int
            {
                return 132;
            }
        };
        $proxy = Fun\lazy(static fn(): object => $x);

        static::assertSame($x, $proxy());
        static::assertSame($x, $proxy());

        static::assertSame(132, $proxy()->doSomething());
        static::assertSame(132, $proxy()->doSomething());
    }

    public function testItCanDealWithNullValues(): void
    {
        $counter = new Psl\Ref(0);
        $proxy = Fun\lazy(static function () use ($counter) {
            $counter->value++;
            if ($counter->value > 1) {
                throw new RuntimeException('The initializer should only be called once');
            }

            return null;
        });

        static::assertSame(null, $proxy());
        static::assertSame(null, $proxy());
        static::assertSame(null, $proxy());
    }

    public function testItCanBeUsedAsALazyEvaluator(): void
    {
        $a = Fun\lazy(static fn(): int => 1);
        for ($i = 1; $i <= 10; $i++) {
            $b = $a;
            $a = Fun\lazy(static fn() => $b() + $b());
        }

        static::assertSame($a(), pow(2, 10));
    }

    public function testItCanBeUsedAsALazyStream(): void
    {
        $incrementalNumbersStream = Fun\lazy(static function () {
            $i = 0;
            while (true) {
                yield ++$i;
            }
        });

        $take = static function (Generator $stream, int $n) {
            $res = [];
            for ($i = 0; $i < $n; $i++) {
                if (!$stream->valid()) {
                    break;
                }
                $res[] = $stream->current();
                $stream->next();
            }
            return $res;
        };

        static::assertSame([1, 2, 3], $take($incrementalNumbersStream(), 3));
        static::assertSame([4, 5, 6], $take($incrementalNumbersStream(), 3));
    }
}
