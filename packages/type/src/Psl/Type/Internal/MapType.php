<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Collection;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_iterable;
use function is_object;
use function sprintf;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<Collection\MapInterface<Tk, Tv>>
 *
 * @internal
 */
final readonly class MapType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tk> $keyType
     * @param Type\TypeInterface<Tv> $valueType
     */
    public function __construct(
        private Type\TypeInterface $keyType,
        private Type\TypeInterface $valueType,
    ) {}

    /**
     * @psalm-assert-if-true Collection\MapInterface<Tk, Tv> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_object($value) || !$value instanceof Collection\MapInterface) {
            return false;
        }

        // @mago-expect analysis:mixed-assignment
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
     * @return Collection\MapInterface<Tk, Tv>
     */
    #[Override]
    public function coerce(mixed $value): Collection\MapInterface
    {
        if (is_iterable($value)) {
            /** @var Type\Type<Tk> $keyType */
            $keyType = $this->keyType;
            /** @var Type\Type<Tv> $valueType */
            $valueType = $this->valueType;

            /** @var list<array{Tk, Tv}> $entries */
            $entries = [];

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

                    $entries[] = [$kResult, $vResult];
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

            /** @var array<Tk, Tv> $dict */
            $dict = [];
            foreach ($entries as [$k, $v]) {
                $dict[$k] = $v;
            }

            return new Collection\Map($dict);
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return Collection\MapInterface<Tk, Tv>
     *
     * @psalm-assert Collection\MapInterface<Tk, Tv> $value
     */
    #[Override]
    public function assert(mixed $value): Collection\MapInterface
    {
        if (is_object($value) && $value instanceof Collection\MapInterface) {
            /** @var Type\Type<Tk> $keyType */
            $keyType = $this->keyType;
            /** @var Type\Type<Tv> $valueType */
            $valueType = $this->valueType;

            /** @var list<array{Tk, Tv}> $entries */
            $entries = [];

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

                    $entries[] = [$kResult, $vResult];
                }
            } catch (AssertException $e) {
                throw match ($tryingKey) {
                    true => AssertException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                    false => AssertException::withValue($v, $this->toString(), PathExpression::path($k), $e),
                };
            }

            /** @var array<Tk, Tv> $dict */
            $dict = [];
            foreach ($entries as [$k, $v]) {
                $dict[$k] = $v;
            }

            return new Collection\Map($dict);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return sprintf(
            '%s<%s, %s>',
            Collection\MapInterface::class,
            $this->keyType->toString(),
            $this->valueType->toString(),
        );
    }
}
