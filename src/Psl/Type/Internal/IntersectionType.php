<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\Exception;
use Psl\Type\Type;
use Psl\Type\TypeInterface;

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
     * @psalm-var TypeInterface<Tl>
     */
    private TypeInterface $left_type;

    /**
     * @psalm-var TypeInterface<Tr>
     */
    private TypeInterface $right_type;

    /**
     * @psalm-param TypeInterface<Tl> $left_type
     * @psalm-param TypeInterface<Tr> $right_type
     */
    public function __construct(
        TypeInterface $left_type,
        TypeInterface $right_type
    ) {
        $this->left_type  = $left_type;
        $this->right_type = $right_type;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true Tl&Tr $value
     */
    public function matches($value): bool
    {
        return $this->right_type->matches($value) && $this->left_type->matches($value);
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
            $value = $this->left_type->coerce($value);
            /** @psalm-var Tl&Tr */
            return $this->right_type->assert($value);
        } catch (Exception $_exception) {
            // ignore
        }

        try {
            /** @psalm-var Tr $value */
            $value = $this->right_type->coerce($value);
            /** @psalm-var Tr&Tl */
            return $this->left_type->assert($value);
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
            $value = $this->left_type->assert($value);
            /** @psalm-var Tl&Tr */
            return $this->right_type->assert($value);
        } catch (AssertException $_exception) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }
    }

    public function toString(): string
    {
        $left  = $this->left_type->toString();
        $right = $this->right_type->toString();
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
