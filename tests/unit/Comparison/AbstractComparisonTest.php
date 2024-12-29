<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Comparison;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Exception\IncomparableException;
use Psl\Comparison\Order;

abstract class AbstractComparisonTest extends TestCase
{
    public static function provideComparisonCases(): Generator
    {
        yield 'scalar-default' => [0, 0, Order::default()];
        yield 'scalar-equal' => [0, 0, Order::Equal];
        yield 'scalar-less' => [0, 1, Order::Less];
        yield 'scalar-greater' => [1, 0, Order::Greater];

        yield 'comparable-default' => [
            self::createComparableIntWrapper(0),
            self::createComparableIntWrapper(0),
            Order::default(),
        ];
        yield 'comparable-equal' => [
            self::createComparableIntWrapper(0),
            self::createComparableIntWrapper(0),
            Order::Equal,
        ];
        yield 'comparable-less' => [
            self::createComparableIntWrapper(0),
            self::createComparableIntWrapper(1),
            Order::Less,
        ];
        yield 'comparable-greater' => [
            self::createComparableIntWrapper(1),
            self::createComparableIntWrapper(0),
            Order::Greater,
        ];
    }

    protected static function createComparableIntWrapper(int $i): Comparable
    {
        return new class($i) implements Comparable {
            public function __construct(
                public readonly int $int,
            ) {
            }
            public function compare(mixed $other): Order
            {
                return Order::from($this->int <=> $other->int);
            }
        };
    }

    protected static function createIncomparableWrapper(int $i, string $additionalInfo = ''): Comparable
    {
        return new class($i, $additionalInfo) implements Comparable {
            public function __construct(
                public readonly int $int,
                public readonly string $additionalInfo,
            ) {
            }

            public function compare(mixed $other): Order
            {
                throw IncomparableException::fromValues($this->int, $other->int, $this->additionalInfo);
            }
        };
    }
}
