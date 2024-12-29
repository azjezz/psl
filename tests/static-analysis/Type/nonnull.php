<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Type;

use Psl\Type;

/**
 * @throws Type\Exception\AssertException
 */
function returns_non_null_assertion(null|string $state): string
{
    return Type\nonnull()->assert($state);
}

/**
 * @throws Type\Exception\AssertException
 */
function returns_non_null_assertion_asserted(null|string $state): string
{
    Type\nonnull()->assert($state);

    return $state;
}

/**
 * @throws Type\Exception\CoercionException
 */
function returns_non_null_coercion(null|string $state): string
{
    return Type\nonnull()->coerce($state);
}

/**
 * @return true
 */
function returns_truthy_match(string $state): bool
{
    return Type\nonnull()->matches($state);
}

/**
 * @return false
 */
function returns_falsy_match(null $state = null): bool
{
    return Type\nonnull()->matches($state);
}

/**
 * @throws Type\Exception\CoercionException
 *
 * Wrapping it in container types still leeds to mixed (including null) return type.
 * This won't be solvable until psalm supports a TNotNull type.
 *
 * @return array<'mightBeNull', mixed>
 */
function returns_mixed_in_shape(mixed $data): array
{
    return Type\shape(['mightBeNull' => Type\nonnull()])->coerce($data);
}
