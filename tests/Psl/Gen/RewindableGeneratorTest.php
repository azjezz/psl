<?php

declare(strict_types=1);

namespace Psl\Tests\Gen;

use PHPUnit\Framework\TestCase;
use Psl\Collection\MutableVector;
use Psl\Gen;

class RewindableGeneratorTest extends TestCase
{
    public function testIterating(): void
    {
        $spy = new MutableVector([]);

        $generator = (static function () use ($spy): iterable {
            for ($i = 0; $i < 3; $i++) {
                $spy->add('generator (' . $i . ')');

                yield ['foo', 'bar'] => $i;
            }
        })();

        $rewindable = Gen\rewindable($generator);
        for ($i = 0; $i < 3; $i++) {
            foreach ($rewindable as $k => $v) {
                $spy->add('foreach (' . $v . ')');

                /**
                 * Assert supports non-array-key keys. ( in this case, keys are arrays )
                 */
                self::assertSame(['foo', 'bar'], $k);
            }
        }

        $rewindable->rewind();
        while ($rewindable->valid()) {
            $spy->add('while (' . $rewindable->current() . ')');

            $rewindable->next();
        }

        /**
         * The following proves that :
         *  - The iterator is capable of rewinding a generator.
         *  - The generator is not exhausted immediately on construction.
         */
        self::assertSame([
            'generator (0)',
            'foreach (0)',
            'generator (1)',
            'foreach (1)',
            'generator (2)',
            'foreach (2)',
            'foreach (0)',
            'foreach (1)',
            'foreach (2)',
            'foreach (0)',
            'foreach (1)',
            'foreach (2)',
            'while (0)',
            'while (1)',
            'while (2)',
        ], $spy->toArray());
    }

    public function testRewindingValidGenerator(): void
    {
        $spy = new MutableVector([]);

        $generator = (static function () use ($spy): iterable {
            for ($i = 0; $i < 3; $i++) {
                $spy->add($e = 'generator (' . $i . ')');
                yield $i;
            }
        })();

        $rewindable = Gen\rewindable($generator);
        foreach ($rewindable as $k => $v) {
            $spy->add('foreach (' . $v . ')');
            break;
        }
        $rewindable->rewind();
        do {
            $spy->add('do while (' . $rewindable->current() . ')');
            break;
        } while ($rewindable->valid());

        $rewindable->rewind();
        while ($rewindable->valid()) {
            $spy->add('while (' . $rewindable->current() . ')');
            break;
        };

        for ($rewindable->rewind(); $rewindable->valid(); $rewindable->next()) {
            $spy->add('for (' . $rewindable->current() . ')');
        }

        self::assertSame([
            'generator (0)',
            'foreach (0)',
            'do while (0)',
            'while (0)',
            'for (0)',
            'generator (1)',
            'for (1)',
            'generator (2)',
            'for (2)',
        ], $spy->toArray());
    }
}
