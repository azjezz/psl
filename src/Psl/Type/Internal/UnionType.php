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
     * @psalm-var Type\TypeInterface<Tl>
     */
    private Type\TypeInterface $left_type;

    /**
     * @psalm-var Type\TypeInterface<Tr>
     */
    private Type\TypeInterface $right_type;

    /**
     * @psalm-param Type\TypeInterface<Tl> $left_type
     * @psalm-param Type\TypeInterface<Tr> $right_type
     */
    public function __construct(
        Type\TypeInterface $left_type,
        Type\TypeInterface $right_type
    ) {
        $this->left_type  = $left_type;
        $this->right_type = $right_type;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true Tl|Tr $value
     */
    public function matches($value): bool
    {
        return $this->left_type->matches($value) || $this->right_type->matches($value);
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Tl|Tr
     *
     * @throws CoercionException
     */
    public function coerce($value)
    {
        try {
            return $this->assert($value);
        } catch (AssertException $_exception) {
            // ignore
        }

        try {
            return $this->left_type->coerce($value);
        } catch (CoercionException $_exception) {
            // ignore
        }

        try {
            return $this->right_type->coerce($value);
        } catch (CoercionException $_exception) {
            // ignore
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Tl|Tr
     *
     * @psalm-assert Tl|Tr $value
     *
     * @throws AssertException
     */
    public function assert($value)
    {
        try {
            return $this->left_type->assert($value);
        } catch (AssertException $_exception) {
            // ignore
        }

        try {
            return $this->right_type->assert($value);
        } catch (AssertException $_exception) {
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
