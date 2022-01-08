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
 * @template Tv
 *
 * @extends Type\Type<non-empty-list<Tv>>
 *
 * @internal
 */
final class NonEmptyVecType extends Type\Type
{
    /**
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @psalm-assert-if-true non-empty-list<Tv> $value
     */
    public function matches(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if ([] === $value) {
            return false;
        }

        $index = 0;
        foreach ($value as $k => $v) {
            if ($index !== $k) {
                return false;
            }

            if (!$this->value_type->matches($v)) {
                return false;
            }

            $index++;
        }

        return true;
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-list<Tv>
     */
    public function coerce(mixed $value): iterable
    {
        if (is_iterable($value)) {
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('non-empty-vec<%s>', $this->value_type->toString()));

            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @var list<Tv> $entries
             */
            $result = [];

            /** @var Tv $v */
            foreach ($value as $v) {
                $result[] = $value_type->coerce($v);
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
     * @return non-empty-list<Tv>
     *
     * @psalm-assert non-empty-list<Tv> $value
     */
    public function assert(mixed $value): array
    {
        if (is_array($value)) {
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('non-empty-vec<%s>', $this->value_type->toString()));

            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            $result = [];
            $index = 0;

            /**
             * @var int $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                if ($index !== $k) {
                    throw AssertException::withValue($value, $this->toString(), $this->getTrace());
                }

                $index++;
                $result[] = $value_type->assert($v);
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
        return Str\format('non-empty-vec<%s>', $this->value_type->toString());
    }
}
