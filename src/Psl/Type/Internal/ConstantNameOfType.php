<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Class;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;

/**
 * @extends Type<non-empty-string>
 *
 * @internal
 */
final readonly class ConstantNameOfType extends Type
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
        return is_string($value) && $value !== '' && Class\has_constant($this->classname, $value);
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    #[Override]
    public function coerce(mixed $value): string
    {
        if (is_string($value) && $value !== '' && Class\has_constant($this->classname, $value)) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString());
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
        if (is_string($value) && $value !== '' && Class\has_constant($this->classname, $value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'constant-name-of<' . $this->classname . '>';
    }
}
