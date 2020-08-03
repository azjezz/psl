<?php declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Iter;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @template T
 */
abstract class TypeTest extends TestCase
{

    /**
     * @psalm-return Type<T>
     */
    abstract public function getType(): Type;

    /**
     * @psalm-return iterable<array{0: mixed, 1: T}>
     */
    abstract public function getValidCoercions(): iterable;

    /**
     * @psalm-return iterable<array{0: mixed}>
     */
    abstract public function getInvalidCoercions(): iterable;

    /**
     * @psalm-return iterable<array{0: Type<mixed>, 1: string}>
     */
    abstract public function getToStringExamples(): iterable;

    /**
     * @psalm-return list<array{0: T}>
     */
    public function getValidValues(): array
    {
        $non_unique = $this->getValidCoercions();
        $non_unique = Iter\map($non_unique, fn ($tuple) => $tuple[1]);

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
     * @psalm-return list<array{0: mixed}>
     */
    public function getInvalidValues(): array
    {
        $rows = $this->getInvalidCoercions();
        $rows = Iter\to_array($rows);
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
     * @psalm-param mixed $value
     * @psalm-param T     $expected
     *
     * @dataProvider getValidCoercions
     */
    final public function testValidCoercion($value, $expected): void
    {
        $actual = $this->getType()->coerce($value);

        self::assertTrue($this->equals($expected, $actual));
        self::assertTrue($this->equals($actual, $this->getType()->coerce($actual)));
    }

    /**
     * @dataProvider getInvalidCoercions
     */
    public function testInvalidCoercion($value): void
    {
        $this->expectException(TypeCoercionException::class);

        $this->getType()->coerce($value);
    }

    /**
     * @dataProvider getValidValues
     */
    final public function testValidAssertion($value): void
    {
        $out = $this->getType()->assert($value);

        self::assertTrue($this->equals($out, $value));
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidAssertion($value): void
    {
        $this->expectException(TypeAssertException::class);

        $this->getType()->assert($value);
    }

    /**
     * @dataProvider getToStringExamples
     */
    final public function testToString(Type $ts, string $expected): void
    {
        self::assertSame($expected, $ts->toString());
    }

    /**
     * @psalm-param T $a
     * @psalm-param T $b
     */
    protected function equals($a, $b): bool
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
