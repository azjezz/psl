<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use BackedEnum;
use Override;
use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Exception\RuntimeException;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;

use function is_a;
use function is_int;
use function is_string;

/**
 * @template T of BackedEnum
 *
 * @extends Type\Type<value-of<T>>
 *
 * @internal
 */
final readonly class BackedEnumValueType extends Type\Type
{
    private bool $isStringBacked;

    /**
     * @psalm-mutation-free
     *
     * @param enum-string<T> $enum
     *
     * @throws RuntimeException If reflection fails.
     * @throws InvariantViolationException If the given value is not enum-string<BackedEnum>.
     */
    public function __construct(
        private string $enum,
    ) {
        $this->isStringBacked = $this->hasStringBackingType($this->enum);
    }

    /**
     * @param enum-string<T> $enum
     *
     * @throws RuntimeException If reflection fails.
     * @throws InvariantViolationException If the given value is not enum-string<BackedEnum>.
     */
    private function hasStringBackingType(string $enum): bool
    {
        Psl\invariant(is_a($enum, BackedEnum::class, true), 'A BackedEnum enum-string is required');

        // If the enum has any cases, detect its type by inspecting the first case found
        $case = $enum::cases()[0] ?? null;
        if (null !== $case) {
            return is_string($case->value);
        }

        // Fallback to reflection to detect the backing type:
        try {
            $reflection = new ReflectionEnum($enum);
            $type = $reflection->getBackingType();
            Psl\invariant($type instanceof ReflectionNamedType, 'Unexpected type');
            return $type->getName() === 'string';
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to reflect an enum enum-string', 0, $e);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @psalm-assert-if-true value-of<T> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return match ($this->isStringBacked) {
            true => is_string($value) && $this->enum::tryFrom($value) !== null,
            false => is_int($value) && $this->enum::tryFrom($value) !== null,
        };
    }

    /**
     * @throws CoercionException
     *
     * @return value-of<T>
     *
     * @mago-expect lint:no-empty-catch-clause
     */
    #[Override]
    public function coerce(mixed $value): string|int
    {
        try {
            $case = $this->isStringBacked ? Type\string()->coerce($value) : Type\int()->coerce($value);

            if ($this->matches($case)) { // @mago-expect analysis:redundant-type-comparison
                return $case;
            }
        } catch (CoercionException) {
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return value-of<T>
     *
     * @psalm-assert value-of<T> $value
     */
    #[Override]
    public function assert(mixed $value): string|int
    {
        if ($this->matches($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'value-of<' . $this->enum . '>';
    }
}
