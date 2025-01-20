<?php

namespace Psl\Tests\Benchmark\Collection;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Collection\MapInterface;
use Psl\Collection\VectorInterface;
use function Psl\Vec\map;

#[Groups(["collection"])]
abstract class AbstractMapBench
{
    public function benchFromArray(): void
    {
        map($this->getDataProviders(), fn ($value) => $this->createFromArray(iterator_to_array($value)));
    }

    public function benchFromIterable(): void
    {
        map($this->getDataProviders(), fn ($value) => $this->createFromIterable($value));
    }

    public function benchmarkValues(): VectorInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->values();
    }

    public function benchmarkKeys(): VectorInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->keys();
    }

    public function benchmarkFilter(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        $map->filter(fn ($v) => $v === 'a');
        $map->filter(fn ($v) => $v === 'k');
        $map->filter(fn ($v) => $v === 'foo');
    }

    public function benchmarkMap(): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->map(fn ($v) => $v);
    }

    public function benchmarkMapWithKey(): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->mapWithKey(fn ($k, $v) => [$k, $v]);
    }

    public function benchmarkFirst(): string|null
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->first();
    }

    public function benchmarkFirstKey(): string|null
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->firstKey();
    }

    public function benchmarkLast(): string|null
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->last();
    }

    public function benchmarkLastKey(): string|null
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->lastKey();
    }

    /**
     * @param array{value: string} $params
     */
    #[ParamProviders('provideLinearSearch')]
    public function benchmarkLinearSearch(array $params): string|null
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->linearSearch($params['value']);
    }

    public function provideLinearSearch(): array
    {
        return [
             ['value' => 'a'],
             ['value' => 'k'],
             ['value' => 'lol'],
        ];
    }

    #[ParamProviders('provideZip')]
    public function benchmarkZip(array $data): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->zip($data);
    }

    public function provideZip(): array
    {
        return [
            ['value' => []],
            ['value' => ['z', 'y', 'x']],
        ];
    }

    /**
     * @param array{value: int<0, max>} $data
     */
    #[ParamProviders('provideTake')]
    public function benchmarkTake(array $data): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->take($data['value']);
    }

    /**
     * @return list<array{value: int<0, max>}>
     */
    public function provideTake(): array
    {
        return [
            ['value' => 0],
            ['value' => 1],
            ['value' => 5],
            ['value' => 1000],
        ];
    }

    public function benchmarkTakeWhile(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        $map->takeWhile(fn ($v) => $v === 'a');
        $map->takeWhile(fn ($v) => $v === 'k');
        $map->takeWhile(fn ($v) => $v === 'rolf');
        $map->takeWhile(fn () => true);
        $map->takeWhile(fn () => false);
    }

    /**
     * @param array{value: int<0, max>} $data
     */
    #[ParamProviders('provideDrop')]
    public function benchmarkDrop(array $data): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->drop($data['value']);
    }

    /**
     * @return list<array{value: int<0, max>}>
     */
    public function provideDrop(): array

    {
        return [
            ['value' => 0],
            ['value' => 1],
            ['value' => 8],
            ['value' => 1000],
        ];
    }

    public function benchmarkDropWhile(): void
    {
        $map = $this->createFromIterable($this->createAssociativeIterable());

        $map->dropWhile(fn ($v) => $v === 'a');
        $map->dropWhile(fn ($v) => $v === 'k');
        $map->dropWhile(fn ($v) => $v === 'rolf');
        $map->dropWhile(fn () => true);
        $map->dropWhile(fn () => false);
    }

    /**
     * @param array{start: int<0, max>, length: int<0, max>} $data
     */
    #[ParamProviders('provideSlice')]
    public function benchmarkSlice(array $data): MapInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->slice($data['start'], $data['length']);
    }

    /**
     * @return list<array{start: int<0, max>, length: int<0, max>}>
     */
    public function provideSlice(): array
    {
        return [
            ['start' => 0, 'length' => 10],
            ['start' => 5, 'length' => 10],
            ['start' => 15, 'length' => 10],
            ['start' => 1000, 'length' => 100],
        ];
    }

    /**
     * @param array{value: positive-int} $data
     */
    #[ParamProviders('provideChunk')]
    public function benchmarkChunk(array $data): VectorInterface
    {
        return $this
            ->createFromIterable($this->createAssociativeIterable())
            ->chunk($data['value']);
    }

    /**
     * @return list<array{value: positive-int}>
     */
    public function provideChunk(): array
    {
        return [
            ['value' => 1],
            ['value' => 2],
            ['value' => 3],
            ['value' => 10],
            ['value' => 1000],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public function getDataProviders(): array
    {
        return [
            'empty array' => [$this->createEmptyIterable()],
            'list' => [$this->createNumericIterable()],
            'associative' => [$this->createAssociativeIterable()],
        ];
    }

    public function createEmptyIterable(): iterable
    {
        yield from [];
    }

    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param iterable<Tk, Tv> $items     *
     * @return MapInterface<Tk, Tv>
     */
    abstract protected function createFromIterable(iterable $items): MapInterface;
    /**
     * @template     Tk of array-key
     * @template     Tv
     *
     * @param array<Tk, Tv> $items
     *
     * @return MapInterface<Tk, Tv>
     */
    abstract protected function createFromArray(array $items): MapInterface;

    /**
     * @return \Generator<int, string, void, void>
     */
    function createNumericIterable(): \Generator
    {
        yield 'a';
        yield 'b';
        yield 'c';
        yield 'd';
        yield 'e';
        yield 'f';
        yield 'g';
        yield 'h';
        yield 'i';
        yield 'j';
        yield 'k';
    }

    /**
     * @return \Generator<string, string, void, void>
     */
    public function createAssociativeIterable(): \Generator
    {
        yield 'a' => 'a';
        yield 'b' => 'b';
        yield 'c' => 'c';
        yield 'd' => 'd';
        yield 'e' => 'e';
        yield 'f' => 'f';
        yield 'g' => 'g';
        yield 'h' => 'h';
        yield 'i' => 'i';
        yield 'j' => 'j';
        yield 'k' => 'k';
    }
}
