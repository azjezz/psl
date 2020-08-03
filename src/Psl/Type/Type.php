<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
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
     * @throws TypeCoercionException
     */
    abstract public function coerce($value);

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws TypeAssertException
     */
    abstract public function assert($value);

    /**
     * Returns a string representation of the type.
     */
    abstract public function toString(): string;
}
