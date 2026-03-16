<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\Exception;
use Psl\Type\Type;
use Psl\Type\TypeInterface;

use function sprintf;
use function str_contains;

/**
 * @template Tl
 * @template Tr
 *
 * @extends Type<Tl&Tr>
 *
 * @internal
 */
final readonly class IntersectionType extends Type
{
    /**
     * @psalm-mutation-free
     *
     * @param TypeInterface<Tl> $left_type
     * @param TypeInterface<Tr> $right_type
     */
    public function __construct(
        private readonly TypeInterface $left_type,
        private readonly TypeInterface $right_type,
    ) {}

    /**
     * @psalm-assert-if-true Tl&Tr $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return $this->right_type->matches($value) && $this->left_type->matches($value);
    }

    /**
     * @throws CoercionException
     *
     * @return Tl&Tr
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
            $value = $this->left_type->coerce($value);
            /** @var Tl&Tr */
            return $this->right_type->assert($value);
        } catch (Exception) {
            // ignore
        }

        try {
            $value = $this->right_type->coerce($value);
            /** @var Tr&Tl */
            return $this->left_type->assert($value);
        } catch (Exception) {
            // ignore
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return Tl&Tr
     *
     * @psalm-assert Tl&Tr $value
     */
    #[Override]
    public function assert(mixed $value): mixed
    {
        try {
            $value = $this->left_type->assert($value);
            /** @var Tl&Tr */
            return $this->right_type->assert($value);
        } catch (AssertException) {
            throw AssertException::withValue($value, $this->toString());
        }
    }

    #[Override]
    public function toString(): string
    {
        $left = $this->left_type->toString();
        $right = $this->right_type->toString();
        if (str_contains($left, '|')) {
            $left = sprintf('(%s)', $left);
        }

        if (str_contains($right, '|')) {
            $right = sprintf('(%s)', $right);
        }

        return sprintf('%s&%s', $left, $right);
    }
}
