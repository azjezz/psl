<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_array;
use function is_iterable;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<non-empty-array<Tk, Tv>>
 *
 * @internal
 */
final class NonEmptyDictType extends Type\Type
{
    /**
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
     * @return non-empty-array<Tk, Tv>
     */
    public function coerce(mixed $value): array
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('non-empty-dict<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('non-empty-dict<_, %s>', $this->value_type->toString()));

            $key_type = $this->key_type->withTrace($key_trace);
            $value_type = $this->value_type->withTrace($value_trace);

            $result = [];

            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $result[$key_type->coerce($k)] = $value_type->coerce($v);
            }

            if ($result === []) {
                throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
            }

            return $result;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return non-empty-array<Tk, Tv>
     *
     * @psalm-assert non-empty-array<Tk, Tv> $value
     */
    public function assert(mixed $value): array
    {
        if (is_array($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('non-empty-dict<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('non-empty-dict<_, %s>', $this->value_type->toString()));

            $key_type = $this->key_type->withTrace($key_trace);
            $value_type = $this->value_type->withTrace($value_trace);

            $result = [];

            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                $result[$key_type->assert($k)] = $value_type->assert($v);
            }

            if ($result === []) {
                throw AssertException::withValue($value, $this->toString(), $this->getTrace());
            }

            return $result;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('non-empty-dict<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
