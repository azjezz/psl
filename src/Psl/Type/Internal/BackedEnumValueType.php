<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use BackedEnum;
use Psl\Exception\InvariantViolationException;
use Psl\Exception\RuntimeException;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;

use function is_a;
use function is_int;
use function is_string;
use function Psl\invariant;
use function Psl\Type\int;
use function Psl\Type\string;

/**
 * @template T of BackedEnum
 *
 * @extends Type<value-of<T>>
 *
 * @internal
 */
final readonly class BackedEnumValueType extends Type
{
    private bool $isStringBacked;

    /**
     * @psalm-mutation-free
     *
     * @param class-string<T> $enum
     *
     * @throws RuntimeException If reflection fails.
     * @throws InvariantViolationException If the given value is not class-string<BackedEnum>.
     */
    public function __construct(
        private string $enum,
    ) {
        $this->isStringBacked = $this->hasStringBackingType($this->enum);
    }

    /**
     * @param class-string<T> $enum
     *
     * @throws RuntimeException If reflection fails.
     * @throws InvariantViolationException If the given value is not class-string<BackedEnum>.
     */
    private function hasStringBackingType(string $enum): bool
    {
        invariant(is_a($enum, BackedEnum::class, true), 'A BackedEnum class-string is required');

        // If the enum has any cases, detect its type by inspecting the first case found
        $case = $enum::cases()[0] ?? null;
        if ($case !== null) {
            return is_string($case->value);
        }

        // Fallback to reflection to detect the backing type:
        try {
            $reflection = new ReflectionEnum($enum);
            $type = $reflection->getBackingType();
            invariant($type instanceof ReflectionNamedType, 'Unexpected type');
            return $type->getName() === 'string';
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to reflect an enum class-string', 0, $e);
        }
    }

    /**
     * @psalm-assert-if-true value-of<T> $value
     */
    #[\Override]
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
     * @psalm-suppress MismatchingDocblockReturnType,DocblockTypeContradiction
     *     Psalm has issues with value-of<T> when used with an enum
     *
     * @mago-ignore best-practices/no-empty-catch-clause
     */
    #[\Override]
    public function coerce(mixed $value): string|int
    {
        try {
            $case = $this->isStringBacked ? string()->coerce($value) : int()->coerce($value);

            if ($this->matches($case)) {
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
     *
     * @psalm-suppress MismatchingDocblockReturnType
     *     Psalm has issues with value-of<T> when used with an enum
     */
    #[\Override]
    public function assert(mixed $value): string|int
    {
        if ($this->matches($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'value-of<' . $this->enum . '>';
    }
}
