<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Collection;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Collection\Map;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;
use Psl\Collection\Vector;
use Psl\Vec;

#[Groups(['collection'])]
final class CollectionBench
{
    /**
     * @param array{vector: Vector, mutable_vector: MutableVector, search: int} $params
     */
    #[ParamProviders('provideVectorData')]
    public function benchVectorLinearSearch(array $params): void
    {
        $_ = $params['vector']->linearSearch($params['search']);
    }

    /**
     * @param array{vector: Vector, mutable_vector: MutableVector, search: int} $params
     */
    #[ParamProviders('provideVectorData')]
    public function benchMutableVectorLinearSearch(array $params): void
    {
        $_ = $params['mutable_vector']->linearSearch($params['search']);
    }

    /**
     * @param array{map: Map, mutable_map: MutableMap, search: int} $params
     */
    #[ParamProviders('provideMapData')]
    public function benchMapLinearSearch(array $params): void
    {
        $_ = $params['map']->linearSearch($params['search']);
    }

    /**
     * @param array{mutable_map: MutableMap, search: mixed} $params
     */
    #[ParamProviders('provideMapData')]
    public function benchMutableMapLinearSearch(array $params): void
    {
        $_ = $params['mutable_map']->linearSearch($params['search']);
    }

    /**
     * @param array{elements: array<int, int>} $params
     */
    #[ParamProviders('provideConstructorData')]
    public function benchVectorConstructor(array $params): void
    {
        $_ = new Vector($params['elements']);
    }

    /**
     * @param array{elements: array<int, int>} $params
     */
    #[ParamProviders('provideConstructorData')]
    public function benchMutableVectorConstructor(array $params): void
    {
        $_ = new MutableVector($params['elements']);
    }

    /**
     * @param array{map: Map, zip_with: array<int, int>} $params
     */
    #[ParamProviders('provideMapZipData')]
    public function benchMapZip(array $params): void
    {
        $params['map']->zip($params['zip_with']);
    }

    /**
     * @param array{map: MutableMap, zip_with: array<int, int>} $params
     */
    #[ParamProviders('provideMutableMapZipData')]
    public function benchMutableMapZip(array $params): void
    {
        $params['map']->zip($params['zip_with']);
    }

    /**
     * @param array{map: Map, chunk_size: positive-int} $params
     */
    #[ParamProviders('provideMapChunkData')]
    public function benchMapChunk(array $params): void
    {
        $params['map']->chunk($params['chunk_size']);
    }

    /**
     * @param array{map: MutableMap, chunk_size: positive-int} $params
     */
    #[ParamProviders('provideMutableMapChunkData')]
    public function benchMutableMapChunk(array $params): void
    {
        $params['map']->chunk($params['chunk_size']);
    }

    /**
     * @return iterable<non-empty-string, array{vector: Vector, mutable_vector: MutableVector, search: int}>
     */
    public function provideVectorData(): iterable
    {
        $small = Vec\range(1, 10);
        $medium = Vec\range(1, 100);
        $large = Vec\range(1, 1000);

        yield 'small (10), found' => [
            'vector' => new Vector($small),
            'mutable_vector' => new MutableVector($small),
            'search' => 5,
        ];
        yield 'medium (100), found' => [
            'vector' => new Vector($medium),
            'mutable_vector' => new MutableVector($medium),
            'search' => 50,
        ];
        yield 'large (1000), found' => [
            'vector' => new Vector($large),
            'mutable_vector' => new MutableVector($large),
            'search' => 500,
        ];
        yield 'large (1000), not found' => [
            'vector' => new Vector($large),
            'mutable_vector' => new MutableVector($large),
            'search' => 9999,
        ];
    }

    /**
     * @return iterable<non-empty-string, array{map: Map, mutable_map: MutableMap, search: int}>
     */
    public function provideMapData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data['key_' . $i] = $i;
            }

            return $data;
        };

        $small = $make(10);
        $medium = $make(100);
        $large = $make(1000);

        yield 'small (10), found' => [
            'map' => new Map($small),
            'mutable_map' => new MutableMap($small),
            'search' => 5,
        ];
        yield 'medium (100), found' => [
            'map' => new Map($medium),
            'mutable_map' => new MutableMap($medium),
            'search' => 50,
        ];
        yield 'large (1000), found' => [
            'map' => new Map($large),
            'mutable_map' => new MutableMap($large),
            'search' => 500,
        ];
    }

    /**
     * @return iterable<non-empty-string, array{elements: array<int, int>}>
     */
    public function provideConstructorData(): iterable
    {
        yield 'small (10)' => ['elements' => Vec\range(1, 10)];
        yield 'medium (100)' => ['elements' => Vec\range(1, 100)];
        yield 'large (1000)' => ['elements' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{map: Map, zip_with: array<int, int>}>
     */
    public function provideMapZipData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data['key_' . $i] = $i;
            }

            return $data;
        };

        yield 'small (10)' => ['map' => new Map($make(10)), 'zip_with' => Vec\range(1, 10)];
        yield 'medium (100)' => ['map' => new Map($make(100)), 'zip_with' => Vec\range(1, 100)];
        yield 'large (1000)' => ['map' => new Map($make(1000)), 'zip_with' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{map: MutableMap, zip_with: array<int, int>}>
     */
    public function provideMutableMapZipData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data['key_' . $i] = $i;
            }

            return $data;
        };

        yield 'small (10)' => ['map' => new MutableMap($make(10)), 'zip_with' => Vec\range(1, 10)];
        yield 'medium (100)' => ['map' => new MutableMap($make(100)), 'zip_with' => Vec\range(1, 100)];
        yield 'large (1000)' => ['map' => new MutableMap($make(1000)), 'zip_with' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{map: Map, chunk_size: positive-int}>
     */
    public function provideMapChunkData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data['key_' . $i] = $i;
            }

            return $data;
        };

        yield 'small (10), chunk 3' => ['map' => new Map($make(10)), 'chunk_size' => 3];
        yield 'medium (100), chunk 10' => ['map' => new Map($make(100)), 'chunk_size' => 10];
        yield 'large (1000), chunk 50' => ['map' => new Map($make(1000)), 'chunk_size' => 50];
    }

    /**
     * @return iterable<non-empty-string, array{map: MutableMap, chunk_size: positive-int}>
     */
    public function provideMutableMapChunkData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data['key_' . $i] = $i;
            }

            return $data;
        };

        yield 'small (10), chunk 3' => ['map' => new MutableMap($make(10)), 'chunk_size' => 3];
        yield 'medium (100), chunk 10' => ['map' => new MutableMap($make(100)), 'chunk_size' => 10];
        yield 'large (1000), chunk 50' => ['map' => new MutableMap($make(1000)), 'chunk_size' => 50];
    }
}
