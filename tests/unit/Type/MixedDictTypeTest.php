<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Generator;
use Psl\Collection;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use SplObjectStorage;
use stdClass;

final class MixedDictTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\mixed_dict();
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [],
            []
        ];

        yield [
            ['foo' => 'bar'],
            ['foo' => 'bar']
        ];

        $object = new stdClass();
        yield [[0,1,2, 'foo' => 'bar', [], $object], [0,1,2, 'foo' => 'bar', [], $object]];

        $gen = $this->generator();
        yield [$gen, [1,2, 'asdf' => 'key']];

        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            new Collection\Vector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map(
                Vec\range(1, 10),
                static fn(int $value): string => Str\format('00%d', $value)
            ),
            ['001', '002', '003', '004', '005', '006', '007', '008', '009', '0010']
        ];

        $spl = new SplObjectStorage();
        $spl[$object] = 'test';
        yield [$spl, [$object]];
    }

    private function generator(): Generator
    {
        yield 1;
        yield 2;
        yield 'asdf' => 'key';
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [new stdClass()];
        yield ['asdf'];
        yield [false];
        yield [null];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'dict<array-key, mixed>'];
    }
}
