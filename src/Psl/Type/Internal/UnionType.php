<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @template Tl
 * @template Tr
 *
 * @extends Type<Tl|Tr>
 *
 * @internal
 */
class UnionType extends Type
{
    /**
     * @psalm-var Type<Tl>
     */
    private Type $left_type_spec;

    /**
     * @psalm-var Type<Tr>
     */
    private Type $right_type_spec;

    /**
     * @psalm-param Type<Tl> $left_type_spec
     * @psalm-param Type<Tr> $right_type_spec
     */
    public function __construct(
        Type $left_type_spec,
        Type $right_type_spec
    ) {
        $this->left_type_spec  = $left_type_spec;
        $this->right_type_spec = $right_type_spec;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Tl|Tr
     *
     * @throws TypeCoercionException
     */
    public function coerce($value)
    {
        try {
            return $this->assert($value);
        } catch (TypeAssertException $_exception) {
            // ignore
        }

        try {
            return $this->left_type_spec->coerce($value);
        } catch (TypeCoercionException $_exception) {
            // ignore
        }

        try {
            return $this->right_type_spec->coerce($value);
        } catch (TypeCoercionException $_exception) {
            // ignore
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Tl|Tr
     *
     * @psalm-assert Tl|Tr $value
     *
     * @throws TypeAssertException
     */
    public function assert($value)
    {
        try {
            return $this->left_type_spec->assert($value);
        } catch (TypeAssertException $_exception) {
            // ignore
        }

        try {
            return $this->right_type_spec->assert($value);
        } catch (TypeAssertException $_exception) {
            // ignore
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        $left  = $this->left_type_spec->toString();
        $right = $this->right_type_spec->toString();
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
