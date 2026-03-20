<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_array;
use function is_iterable;

/**
 * @extends Type\Type<array<array-key, mixed>>
 *
 * @internal
 */
final readonly class MixedDictType extends Type\Type
{
    /**
     * @throws CoercionException
     *
     * @return array<array-key, mixed>
     */
    #[Override]
    public function coerce(mixed $value): array
    {
        if (!is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        if (is_array($value)) {
            return $value;
        }

        $result = [];

        $keyType = Type\array_key();
        $k = null;
        /** @var bool $iterating */
        $iterating = true;

        try {
            /**
             * @var array-key $k
             * @var mixed $v
             */
            foreach ($value as $k => $v) {
                $iterating = false;
                $result[$keyType->coerce($k)] = $v;
                $iterating = true;
            }
        } catch (Throwable $e) {
            throw match (true) {
                $iterating => CoercionException::withValue(
                    null,
                    $this->toString(),
                    PathExpression::iteratorError($k),
                    $e,
                ),
                default => CoercionException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
            };
        }

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return array<array-key, mixed>
     *
     * @psalm-assert array<array-key, mixed> $value
     */
    #[Override]
    public function assert(mixed $value): array
    {
        if (!is_array($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        return $value;
    }

    #[Override]
    public function toString(): string
    {
        return 'dict<array-key, mixed>';
    }
}
