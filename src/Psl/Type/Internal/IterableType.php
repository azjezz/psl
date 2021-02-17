<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_iterable;

/**
 * @template Tk
 * @template Tv
 *
 * @extends Type\Type<iterable<Tk, Tv>>
 *
 * @internal
 */
final class IterableType extends Type\Type
{
    /**
     * @psalm-var Type\TypeInterface<Tk>
     */
    private Type\TypeInterface $key_type;

    /**
     * @psalm-var Type\TypeInterface<Tv>
     */
    private Type\TypeInterface $value_type;

    /**
     * @psalm-param Type\TypeInterface<Tk> $key_type
     * @psalm-param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        Type\TypeInterface $key_type,
        Type\TypeInterface $value_type
    ) {
        $this->key_type   = $key_type;
        $this->value_type = $value_type;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return iterable<Tk, Tv>
     *
     * @throws CoercionException
     */
    public function coerce($value): iterable
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('iterable<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('iterable<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type_speec */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<array{0: Tk, 1: Tv}> $entries
             */
            $entries = [];

            /**
             * @psalm-var Tk $k
             * @psalm-var Tv $v
             */
            foreach ($value as $k => $v) {
                $entries[] = [
                    $key_type->coerce($k),
                    $value_type->coerce($v),
                ];
            }

            /** @var iterable<Tk, Tv> */
            return Iter\Iterator::from((static function () use ($entries) {
                /**
                 * @var Tk $key
                 * @var Tv $value
                 */
                foreach ($entries as [$key, $value]) {
                    yield $key => $value;
                }
            }));
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return iterable<Tk, Tv>
     *
     * @psalm-assert iterable<Tk, Tv> $value
     *
     * @throws AssertException
     */
    public function assert($value): iterable
    {
        if (is_iterable($value)) {
            $key_trace   = $this->getTrace()
                ->withFrame(Str\format('iterable<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()
                ->withFrame(Str\format('iterable<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<array{0: Tk, 1: Tv}> $entries
             */
            $entries = [];

            /**
             * @psalm-var Tk $k
             * @psalm-var Tv $v
             */
            foreach ($value as $k => $v) {
                $entries[] = [
                    $key_type->assert($k),
                    $value_type->assert($v),
                ];
            }

            /** @var iterable<Tk, Tv> */
            return Iter\Iterator::from((static function () use ($entries) {
                /**
                 * @var Tk $key
                 * @var Tv $value
                 */
                foreach ($entries as [$key, $value]) {
                    yield $key => $value;
                }
            }));
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('iterable<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
