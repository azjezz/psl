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
use function sprintf;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<non-empty-array<Tk, Tv>>
 *
 * @internal
 */
final readonly class NonEmptyDictType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tk> $keyType
     * @param Type\TypeInterface<Tv> $valueType
     */
    public function __construct(
        private readonly Type\TypeInterface $keyType,
        private readonly Type\TypeInterface $valueType,
    ) {}

    /**
     * @throws CoercionException
     *
     * @return non-empty-array<Tk, Tv>
     */
    #[Override]
    public function coerce(mixed $value): array
    {
        if (is_iterable($value)) {
            $keyType = $this->keyType;
            $valueType = $this->valueType;

            $result = [];

            $k = null;
            $v = null;
            /** @var bool */
            $tryingKey = true;
            /** @var bool */
            $iterating = true;

            try {
                /**
                 * @var Tk $k
                 * @var Tv $v
                 */
                foreach ($value as $k => $v) {
                    $iterating = false;
                    $tryingKey = true;
                    $kResult = $keyType->coerce($k);
                    $tryingKey = false;
                    $vResult = $valueType->coerce($v);

                    $result[$kResult] = $vResult;
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
                    $tryingKey => CoercionException::withValue(
                        $k,
                        $this->toString(),
                        PathExpression::iteratorKey($k),
                        $e,
                    ),
                    !$tryingKey => CoercionException::withValue($v, $this->toString(), PathExpression::path($k), $e),
                };
            }

            if ([] === $result) {
                throw CoercionException::withValue($value, $this->toString());
            }

            return $result;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return non-empty-array<Tk, Tv>
     *
     * @psalm-assert non-empty-array<Tk, Tv> $value
     */
    #[Override]
    public function assert(mixed $value): array
    {
        if (is_array($value)) {
            $keyType = $this->keyType;
            $valueType = $this->valueType;

            $result = [];

            $k = null;
            $v = null;
            $tryingKey = true;

            try {
                /**
                 * @var Tk $k
                 * @var Tv $v
                 */
                foreach ($value as $k => $v) {
                    $tryingKey = true;
                    $kResult = $keyType->assert($k);
                    $tryingKey = false;
                    $vResult = $valueType->assert($v);

                    $result[$kResult] = $vResult;
                }
            } catch (AssertException $e) {
                throw match ($tryingKey) {
                    true => AssertException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                    false => AssertException::withValue($v, $this->toString(), PathExpression::path($k), $e),
                };
            }

            if ([] === $result) {
                throw AssertException::withValue($value, $this->toString());
            }

            return $result;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return sprintf('non-empty-dict<%s, %s>', $this->keyType->toString(), $this->valueType->toString());
    }
}
