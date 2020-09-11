<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\Exception;
use Psl\Type\Type;

/**
 * @template Tl
 * @template Tr
 *
 * @extends Type<Tl&Tr>
 *
 * @internal
 */
final class IntersectionType extends Type
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
     * @psalm-return Tl&Tr
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
            /** @psalm-var Tl $value */
            $value = $this->left_type_spec->coerce($value);
            /** @psalm-var Tl&Tr */
            return $this->right_type_spec->assert($value);
        } catch (Exception $_exception) {
            // ignore
        }

        try {
            /** @psalm-var Tr $value */
            $value = $this->right_type_spec->coerce($value);
            /** @psalm-var Tr&Tl */
            return $this->left_type_spec->assert($value);
        } catch (Exception $_exception) {
            // ignore
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Tl&Tr
     *
     * @psalm-assert Tl&Tr $value
     *
     * @throws AssertException
     */
    public function assert($value)
    {
        try {
            /** @psalm-var Tl $value */
            $value = $this->left_type_spec->assert($value);
            /** @psalm-var Tl&Tr */
            return $this->right_type_spec->assert($value);
        } catch (AssertException $_exception) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }
    }

    public function toString(): string
    {
        $left  = $this->left_type_spec->toString();
        $right = $this->right_type_spec->toString();
        /** @psalm-suppress MissingThrowsDocblock - offset is within bound. */
        if (Str\contains($left, '|')) {
            $left = Str\format('(%s)', $left);
        }
        /** @psalm-suppress MissingThrowsDocblock - offset is within bound. */
        if (Str\contains($right, '|')) {
            $right = Str\format('(%s)', $right);
        }

        return Str\format('%s&%s', $left, $right);
    }
}
