<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;

/**
 * @template T as object
 *
 * @extends Type<class-string<T>>
 *
 * @internal
 */
final class ClassStringType extends Type
{
    /**
     * @var class-string<T> $classname
     */
    private string $classname;

    /**
     * @param class-string<T> $classname
     */
    public function __construct(
        string $classname
    ) {
        $this->classname = $classname;
    }

    /**
     * @psalm-assert-if-true class-string<T> $value
     */
    public function matches(mixed $value): bool
    {
        return is_string($value) && is_a($value, $this->classname, true);
    }

    /**
     * @throws CoercionException
     *
     * @return class-string<T>
     */
    public function coerce(mixed $value): string
    {
        if (is_string($value) && is_a($value, $this->classname, true)) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return class-string<T>
     *
     * @psalm-assert class-string<T> $value
     */
    public function assert(mixed $value): string
    {
        if (is_string($value) && is_a($value, $this->classname, true)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'class-string<' . $this->classname . '>';
    }
}
