<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

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
     * @psalm-var Type\TypeInterface<Tv>
     */
    private Type\TypeInterface $value_type;

    /**
     * @psalm-param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        Type\TypeInterface $value_type
    ) {
        $this->value_type = $value_type;
    }

    /**
     * @param mixed $value
     *
     * @psalm-assert-if-true list<Tv> $value
     */
    public function matches($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if ([] === $value) {
            return true;
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
     * @psalm-param mixed $value
     *
     * @psalm-return list<Tv>
     *
     * @throws CoercionException
     */
    public function coerce($value): iterable
    {
        if (is_iterable($value)) {
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('list<%s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<Tv> $entries
             */
            $result = [];

            /** @psalm-var Tv $v */
            foreach ($value as $v) {
                $result[] = $value_type->coerce($v);
            }

            return $result;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return list<Tv>
     *
     * @psalm-assert list<Tv> $value
     *
     * @throws AssertException
     */
    public function assert($value): array
    {
        if (is_array($value)) {
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('list<%s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            $result = [];
            $index = 0;

            /**
             * @var int $k
             * @var Tv  $v
             */
            foreach ($value as $k => $v) {
                if ($index !== $k) {
                    throw AssertException::withValue($value, $this->toString(), $this->getTrace());
                }

                $index++;
                $result[] = $value_type->assert($v);
            }

            return $result;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('list<%s>', $this->value_type->toString());
    }
}
