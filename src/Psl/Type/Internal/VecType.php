<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function array_is_list;
use function is_array;
use function is_iterable;

/**
 * @template Tv
 *
 * @extends Type\Type<list<Tv>>
 *
 * @internal
 */
final class VecType extends Type\Type
{
    /**
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @psalm-assert-if-true list<Tv> $value
     */
    public function matches(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }

        foreach ($value as $v) {
            if (!$this->value_type->matches($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws CoercionException
     *
     * @return list<Tv>
     */
    public function coerce(mixed $value): iterable
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        /**
         * @var list<Tv> $entries
         */
        $result = [];
        $value_type = $this->value_type;
        $i = $v = null;

        try {
            /**
             * @var Tv $v
             * @var array-key $i
             */
            foreach ($value as $i => $v) {
                $result[] = $value_type->coerce($v);
            }
        } catch (CoercionException $e) {
            throw match (true) {
                $i === null => $e,
                default => CoercionException::withValue($v, $this->toString(), (string) $i, $e)
            };
        }

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return list<Tv>
     *
     * @psalm-assert list<Tv> $value
     */
    public function assert(mixed $value): array
    {
        if (! is_array($value) || !array_is_list($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        $result = [];
        $value_type = $this->value_type;
        $i = $v = null;

        try {
            /**
             * @var Tv $v
             * @var array-key $i
             */
            foreach ($value as $i => $v) {
                $result[] = $value_type->assert($v);
            }
        } catch (AssertException $e) {
            throw match (true) {
                $i === null => $e,
                default => AssertException::withValue($v, $this->toString(), (string) $i, $e)
            };
        }

        return $result;
    }

    public function toString(): string
    {
        return 'vec<' . $this->value_type->toString() . '>';
    }
}
