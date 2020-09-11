<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\TypeTrace;
use Psl\Type\Internal\TypeTraceTrait;

/**
 * @template T
 */
abstract class Type
{
    use TypeTraceTrait;

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws CoercionException
     */
    abstract public function coerce($value);

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    abstract public function assert($value);

    /**
     * Returns a string representation of the type.
     */
    abstract public function toString(): string;
}
