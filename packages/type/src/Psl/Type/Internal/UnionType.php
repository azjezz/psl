<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function sprintf;
use function str_contains;

/**
 * @template Tl
 * @template Tr
 *
 * @extends Type\Type<Tl|Tr>
 *
 * @internal
 */
readonly class UnionType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tl> $left_type
     * @param Type\TypeInterface<Tr> $right_type
     */
    public function __construct(
        private readonly Type\TypeInterface $left_type,
        private readonly Type\TypeInterface $right_type,
    ) {}

    /**
     * @psalm-assert-if-true Tl|Tr $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return $this->left_type->matches($value) || $this->right_type->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @return Tl|Tr
     *
     * @mago-expect lint:no-empty-catch-clause
     */
    #[Override]
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

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return Tl|Tr
     *
     * @psalm-assert Tl|Tr $value
     *
     * @mago-expect lint:no-empty-catch-clause
     */
    #[Override]
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

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        $left = $this->left_type->toString();
        $right = $this->right_type->toString();
        if (str_contains($left, '&')) {
            $left = sprintf('(%s)', $left);
        }

        if (str_contains($right, '&')) {
            $right = sprintf('(%s)', $right);
        }

        return sprintf('%s|%s', $left, $right);
    }
}
