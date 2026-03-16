<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_iterable;
use function sprintf;

/**
 * @template Tk as array-key
 * @template Tv
 *
 * @extends Type\Type<iterable<Tk, Tv>>
 *
 * @internal
 */
final readonly class ContainerType extends Type\Type
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
     * @psalm-assert-if-true iterable<Tk, Tv> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_iterable($value)) {
            return false;
        }

        // @mago-expect analysis:mixed-assignment,mixed-assignment
        foreach ($value as $k => $v) {
            if (!$this->keyType->matches($k) || !$this->valueType->matches($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws CoercionException
     *
     * @return iterable<Tk, Tv>
     */
    #[Override]
    public function coerce(mixed $value): iterable
    {
        if (!is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        /** @var Type\Type<Tk> $keyType */
        $keyType = $this->keyType;
        /** @var Type\Type<Tv> $value_type_speec */
        $valueType = $this->valueType;

        /** @var array<Tk, Tv> $values */
        $values = [];

        $k = null;
        $v = null;
        /** @var bool $tryingKey */
        $tryingKey = true;
        /** @var bool $iterating */
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

                $values[$kResult] = $vResult;
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
                $tryingKey => CoercionException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                !$tryingKey => CoercionException::withValue($v, $this->toString(), PathExpression::path($k), $e),
            };
        }

        /** @var iterable<Tk, Tv> */
        return $values;
    }

    /**
     * @throws AssertException
     *
     * @return iterable<Tk, Tv>
     *
     * @psalm-assert iterable<Tk, Tv> $value
     */
    #[Override]
    public function assert(mixed $value): iterable
    {
        if (!is_iterable($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        /** @var Type\Type<Tk> $keyType */
        $keyType = $this->keyType;
        /** @var Type\Type<Tv> $valueType */
        $valueType = $this->valueType;

        /** @var array<Tk, Tv> $$values */
        $values = [];

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

                $values[$kResult] = $vResult;
            }
        } catch (AssertException $e) {
            throw match ($tryingKey) {
                true => AssertException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                false => AssertException::withValue($v, $this->toString(), PathExpression::path($k), $e),
            };
        }

        /** @var iterable<Tk, Tv> */
        return $values;
    }

    #[Override]
    public function toString(): string
    {
        return sprintf('container<%s, %s>', $this->keyType->toString(), $this->valueType->toString());
    }
}
