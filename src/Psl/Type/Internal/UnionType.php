<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template Tl
 * @template Tr
 *
 * @extends Type\Type<Tl|Tr>
 *
 * @internal
 */
class UnionType extends Type\Type
{
    /**
     * @param Type\TypeInterface<Tl> $left_type
     * @param Type\TypeInterface<Tr> $right_type
     */
    public function __construct(
        private readonly Type\TypeInterface $left_type,
        private readonly Type\TypeInterface $right_type
    ) {
    }

    /**
     * @psalm-assert-if-true Tl|Tr $value
     */
    public function matches(mixed $value): bool
    {
        return $this->left_type->matches($value) || $this->right_type->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @return Tl|Tr
     */
    public function coerce(mixed $value): mixed
    {
        try {
            return $this->assert($value);
        } catch (AssertException) {
            // ignore
        }

        try {
            return $this->left_type->coerce($value);
        } catch (CoercionException) {
            // ignore
        }

        try {
            return $this->right_type->coerce($value);
        } catch (CoercionException) {
            // ignore
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return Tl|Tr
     *
     * @psalm-assert Tl|Tr $value
     */
    public function assert(mixed $value): mixed
    {
        try {
            return $this->left_type->assert($value);
        } catch (AssertException) {
            // ignore
        }

        try {
            return $this->right_type->assert($value);
        } catch (AssertException) {
            // ignore
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        $left  = $this->left_type->toString();
        $right = $this->right_type->toString();
        /** @psalm-suppress MissingThrowsDocblock - offset is within bound. */
        if (Str\contains($left, '&')) {
            $left = Str\format('(%s)', $left);
        }
        /** @psalm-suppress MissingThrowsDocblock - offset is within bound. */
        if (Str\contains($right, '&')) {
            $right = Str\format('(%s)', $right);
        }

        return Str\format('%s|%s', $left, $right);
    }
}
