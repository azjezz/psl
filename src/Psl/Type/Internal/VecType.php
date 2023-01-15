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
            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        /** @var Type\Type<Tv> $value_type */
        $value_type = $this->value_type->withTrace(
            $this->getTrace()
                ->withFrame($this->toString())
        );

        /**
         * @var list<Tv> $entries
         */
        $result = [];

        /** @var Tv $v */
        foreach ($value as $v) {
            $result[] = $value_type->coerce($v);
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
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }

        /** @var Type\Type<Tv> $value_type */
        $value_type = $this->value_type->withTrace(
            $this->getTrace()
                ->withFrame('vec<' . $this->value_type->toString() . '>')
        );

        $result = [];

        /**
         * @var Tv $v
         */
        foreach ($value as $v) {
            $result[] = $value_type->assert($v);
        }

        return $result;
    }

    public function toString(): string
    {
        return 'vec<' . $this->value_type->toString() . '>';
    }
}
