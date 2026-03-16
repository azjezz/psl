<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;

/**
 * @extends Type\Type<int<-128, 127>>
 *
 * @internal
 */
final readonly class I8Type extends Type\Type
{
    /**
     * @psalm-assert-if-true int<-128, 127> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= -128 && $value <= 127;
    }

    /**
     * @throws CoercionException
     *
     * @return int<-128, 127>
     */
    #[Override]
    public function coerce(mixed $value): int
    {
        $integer = Type\int()->coerce($value);

        if ($integer >= -128 && $integer <= 127) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert int<-128, 127> $value
     *
     * @throws AssertException
     *
     * @return int<-128, 127>
     */
    #[Override]
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= -128 && $value <= 127) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'i8';
    }
}
