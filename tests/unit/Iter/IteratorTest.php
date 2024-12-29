<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Collection\MutableVector;
use Psl\Iter;

final class IteratorTest extends TestCase
{
    public function testCreateFromGenerator(): void
    {
        $iterator = Iter\Iterator::create((static fn() => yield from [1, 2, 3])());

        static::assertCount(3, $iterator);
    }

    public function testCreateFromFactory(): void
    {
        $iterator = Iter\Iterator::from(static fn() => yield from [1, 2, 3]);

        static::assertCount(3, $iterator);
    }

    public function testKeyIteration(): void
    {
        $iterator = Iter\Iterator::from(static fn() => yield from [1, 2, 3]);
        $keys = [];
        while ($iterator->valid()) {
            $keys[] = $iterator->key();

            $iterator->next();
        }

        static::assertSame([0, 1, 2], $keys);
    }

    public function testSeek(): void
    {
        $iterator = new Iter\Iterator((static fn() => yield from [1, 2, 3, 4, 5])());

        static::assertSame(1, $iterator->current());
        $iterator->next();
        static::assertSame(2, $iterator->current());
        $iterator->next();

        $iterator->seek(0);
        static::assertSame(1, $iterator->current());

        $iterator->seek(4);
        static::assertSame(5, $iterator->current());

        static::assertSame(5, $iterator->count());

        $iterator->seek(1);
        static::assertSame(2, $iterator->current());

        $iterator->seek(4);
        static::assertSame(5, $iterator->current());

        static::assertSame(5, $iterator->count());
    }

    public function testSeekThrowsForOutOfBoundIndex(): void
    {
        $iterator = new Iter\Iterator((static fn() => yield from [1, 2, 3, 4, 5])());

        $this->expectException(Iter\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Position is out-of-bounds.');

        $iterator->seek(30);
    }

    public function testSeekThrowsForPlusOneOutOfBoundIndexWhenCached(): void
    {
        $iterator = new Iter\Iterator((static fn() => yield from [1, 2, 3, 4, 5])());

        static::assertSame(5, $iterator->count());

        $this->expectException(Iter\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Position is out-of-bounds.');

        $iterator->seek(5);
    }

    public function testSeekThrowsForPlusOneOutOfBoundIndex(): void
    {
        $iterator = new Iter\Iterator((static fn() => yield from [1, 2, 3, 4, 5])());

        $this->expectException(Iter\Exception\OutOfBoundsException::class);
        $this->expectExceptionMessage('Position is out-of-bounds.');

        $iterator->seek(5);
    }

    public function testSeekZero(): void
    {
        $iterator = new Iter\Iterator((static function () {
            yield 1;

            throw new Exception('nope');

            yield 2;
        })());

        foreach ($iterator as $k => $v) {
            static::assertSame(0, $k);
            static::assertSame(1, $v);
            break;
        }

        $iterator->seek(0);
    }

    public function testIterating(): void
    {
        $spy = new MutableVector([]);

        $generator = (static function () use ($spy): iterable {
            for ($i = 0; $i < 3; $i++) {
                $spy->add('generator (' . $i . ')');

                yield ['foo', 'bar'] => $i;
            }
        })();

        $rewindable = Iter\rewindable($generator);
        for ($i = 0; $i < 3; $i++) {
            foreach ($rewindable as $k => $v) {
                $spy->add('foreach (' . $v . ')');

                /**
                 * Assert supports non-array-key keys. ( in this case, keys are arrays ).
                 */
                static::assertSame(['foo', 'bar'], $k);
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
        static::assertSame(
            [
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
            ],
            $spy->toArray(),
        );
    }

    public function testCountWhileIterating(): void
    {
        $spy = new MutableVector([]);

        $generator = (static function () use ($spy): iterable {
            for ($i = 0; $i < 3; $i++) {
                $spy->add('sending (' . $i . ')');

                yield ['foo', 'bar'] => $i;
            }
        })();

        $rewindable = Iter\rewindable($generator);
        foreach ($rewindable as $key => $value) {
            $spy->add('count (' . $rewindable->count() . ')');
            $spy->add('received (' . $value . ')');

            static::assertSame(['foo', 'bar'], $key);
        }

        static::assertSame(
            [
                'sending (0)',
                'sending (1)',
                'sending (2)',
                'count (3)',
                'received (0)',
                'count (3)',
                'received (1)',
                'count (3)',
                'received (2)',
            ],
            $spy->toArray(),
        );
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

        $rewindable = Iter\rewindable($generator);
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
        }

        for ($rewindable->rewind(); $rewindable->valid(); $rewindable->next()) {
            $spy->add('for (' . $rewindable->current() . ')');
        }

        static::assertSame(
            [
                'generator (0)',
                'foreach (0)',
                'do while (0)',
                'while (0)',
                'for (0)',
                'generator (1)',
                'for (1)',
                'generator (2)',
                'for (2)',
            ],
            $spy->toArray(),
        );
    }
}
