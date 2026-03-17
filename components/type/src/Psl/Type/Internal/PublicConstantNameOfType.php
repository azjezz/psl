<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;
use ReflectionClass;

/**
 * @extends Type<non-empty-string>
 *
 * @internal
 */
final readonly class PublicConstantNameOfType extends Type
{
    /**
     * @var class-string
     */
    private string $classname;

    /**
     * @psalm-mutation-free
     *
     * @param class-string $classname
     */
    public function __construct(string $classname)
    {
        $this->classname = $classname;
    }

    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $reflection = new ReflectionClass($this->classname);
        if (!$reflection->hasConstant($value)) {
            return false;
        }

        $constant = $reflection->getReflectionConstant($value);
        return $constant !== false && $constant->isPublic();
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    #[Override]
    public function coerce(mixed $value): string
    {
        if (!is_string($value) || $value === '') {
            throw CoercionException::withValue($value, $this->toString());
        }

        $reflection = new ReflectionClass($this->classname);
        if (!$reflection->hasConstant($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        $constant = $reflection->getReflectionConstant($value);
        if ($constant === false || !$constant->isPublic()) {
            throw CoercionException::withValue($value, $this->toString());
        }

        return $value;
    }

    /**
     * @throws AssertException
     *
     * @return non-empty-string
     *
     * @psalm-assert non-empty-string $value
     */
    #[Override]
    public function assert(mixed $value): string
    {
        if (!is_string($value) || $value === '') {
            throw AssertException::withValue($value, $this->toString());
        }

        $reflection = new ReflectionClass($this->classname);
        if (!$reflection->hasConstant($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        $constant = $reflection->getReflectionConstant($value);
        if ($constant === false || !$constant->isPublic()) {
            throw AssertException::withValue($value, $this->toString());
        }

        return $value;
    }

    #[Override]
    public function toString(): string
    {
        return 'public-constant-name-of<' . $this->classname . '>';
    }
}
