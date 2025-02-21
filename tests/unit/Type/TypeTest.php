<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;
use Psl\Type\TypeInterface;
use Psl\Vec;

/**
 * @template T
 */
abstract class TypeTest extends TestCase
{
    /**
     * @return TypeInterface<T>
     */
    abstract public function getType(): TypeInterface;

    /**
     * @return iterable<array{0: mixed, 1: T}>
     */
    abstract public function getValidCoercions(): iterable;

    /**
     * @return iterable<array{0: mixed}>
     */
    abstract public function getInvalidCoercions(): iterable;

    /**
     * @return iterable<array{0: Type<mixed>, 1: string}>
     */
    abstract public function getToStringExamples(): iterable;

    /**
     * @return list<array{0: T}>
     */
    public function getValidValues(): array
    {
        $non_unique = $this->getValidCoercions();
        $non_unique = Dict\map($non_unique, static fn(array $tuple): mixed => $tuple[1]);

        $out = [];
        foreach ($non_unique as $v) {
            foreach ($out as $value) {
                if ($this->equals($value, $v)) {
                    break;
                }
            }

            $out[] = [$v];
        }

        return $out;
    }

    /**
     * @return list<array{0: mixed}>
     */
    public function getInvalidValues(): array
    {
        $rows = $this->getInvalidCoercions();
        $rows = Vec\values($rows);
        foreach ($this->getValidCoercions() as $arr) {
            [$value, $v] = $arr;
            if ($this->equals($v, $value)) {
                continue;
            }

            $rows[] = [$value];
        }

        return $rows;
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testMatches($value): void
    {
        static::assertTrue($this->getType()->matches($value));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getInvalidValues
     */
    public function testInvalidMatches($value): void
    {
        static::assertFalse($this->getType()->matches($value));
    }

    /**
     * @param mixed $value
     * @param T $expected
     *
     * @dataProvider getValidCoercions
     */
    final public function testValidCoercion($value, $expected): void
    {
        $actual = $this->getType()->coerce($value);

        static::assertTrue($this->equals($expected, $actual));
        static::assertTrue($this->equals($actual, $this->getType()->coerce($actual)));
    }

    /**
     * @dataProvider getInvalidCoercions
     */
    public function testInvalidCoercion($value): void
    {
        $this->expectException(CoercionException::class);

        try {
            $ret = $this->getType()->coerce($value);
        } catch (CoercionException $e) {
            throw $e;
        }
    }

    /**
     * @dataProvider getValidValues
     */
    final public function testValidAssertion($value): void
    {
        $out = $this->getType()->assert($value);

        static::assertTrue($this->equals($out, $value));
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidAssertion($value): void
    {
        $this->expectException(AssertException::class);

        $this->getType()->assert($value);
    }

    /**
     * @dataProvider getToStringExamples
     */
    final public function testToString(Type $ts, string $expected): void
    {
        static::assertSame($expected, $ts->toString());
    }

    /**
     * @param T $a
     * @param T $b
     */
    protected function equals(mixed $a, mixed $b): bool
    {
        return $a === $b;
    }

    protected function stringable(string $value): object
    {
        return new class($value) {
            private string $value;

            public function __construct(string $value)
            {
                $this->value = $value;
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }
}
