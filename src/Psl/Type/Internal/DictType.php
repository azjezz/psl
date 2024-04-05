<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_array;
use function is_iterable;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 *
 * @internal
 */
final readonly class DictType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tk> $key_type
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $key_type,
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    public function coerce(mixed $value): array
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        $result = [];
        $key_type = $this->key_type;
        $value_type = $this->value_type;

        $k = $v = null;
        $trying_key = true;
        $iterating = true;

        try {
            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $iterating = false;
                $trying_key = true;
                $k_result = $key_type->coerce($k);
                $trying_key = false;
                $v_result = $value_type->coerce($v);

                $result[$k_result] = $v_result;
                $iterating = true;
            }
        } catch (Throwable $e) {
            throw match (true) {
                $iterating => CoercionException::withValue(null, $this->toString(), PathExpression::iteratorError($k), $e),
                $trying_key => CoercionException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                !$trying_key => CoercionException::withValue($v, $this->toString(), PathExpression::path($k), $e)
            };
        }

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     */
    public function assert(mixed $value): array
    {
        if (! is_array($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        $result = [];
        $key_type = $this->key_type;
        $value_type = $this->value_type;

        $k = $v = null;
        $trying_key = true;

        try {
            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $trying_key = true;
                $k_result = $key_type->assert($k);
                $trying_key = false;
                $v_result = $value_type->assert($v);

                $result[$k_result] = $v_result;
            }
        } catch (AssertException $e) {
            throw match ($trying_key) {
                true => AssertException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                false => AssertException::withValue($v, $this->toString(), PathExpression::path($k), $e)
            };
        }

        return $result;
    }

    public function toString(): string
    {
        return 'dict<' . $this->key_type->toString() . ', ' . $this->value_type->toString() . '>';
    }
}
