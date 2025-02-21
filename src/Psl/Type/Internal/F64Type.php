<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function Psl\Type;

/**
 * @ara-extends Type\Type<f64>
 *
 * @extends Type\Type<float>
 *
 * @internal
 */
final readonly class F64Type extends Type\Type
{
    /**
     * @ara-assert-if-true f64 $value
     *
     * @psalm-assert-if-true float $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return Type\float()->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @ara-return f64
     *
     * @return float
     */
    #[\Override]
    public function coerce(mixed $value): float
    {
        return Type\float()->coerce($value);
    }

    /**
     * @ara-assert f64 $value
     *
     * @psalm-assert float $value
     *
     * @throws AssertException
     *
     * @ara-return f64
     *
     * @return float
     */
    #[\Override]
    public function assert(mixed $value): float
    {
        return Type\float()->assert($value);
    }

    #[\Override]
    public function toString(): string
    {
        return 'f64';
    }
}
