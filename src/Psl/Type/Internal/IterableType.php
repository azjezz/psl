<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Iter;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_iterable;
use function sprintf;

/**
 * @template Tk
 * @template Tv
 *
 * @extends Type\Type<iterable<Tk, Tv>>
 *
 * @internal
 */
final readonly class IterableType extends Type\Type
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
                $tryingKey => CoercionException::withValue($k, $this->toString(), PathExpression::iteratorKey($k), $e),
                !$tryingKey => CoercionException::withValue($v, $this->toString(), PathExpression::path($k), $e),
            };
        }

        /** @var iterable<Tk, Tv> */
        return Iter\Iterator::from(static function () use ($entries): iterable {
            foreach ($entries as [$key, $value]) {
                yield $key => $value;
            }
        });
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

        /** @var iterable<Tk, Tv> */
        return Iter\Iterator::from(static function () use ($entries): iterable {
            foreach ($entries as [$key, $value]) {
                yield $key => $value;
            }
        });
    }

    #[Override]
    public function toString(): string
    {
        return sprintf('iterable<%s, %s>', $this->keyType->toString(), $this->valueType->toString());
    }
}
