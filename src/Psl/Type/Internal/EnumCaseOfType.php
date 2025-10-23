<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;
use ReflectionEnum;

/**
 * @extends Type<non-empty-string>
 *
 * @internal
 */
final readonly class EnumCaseOfType extends Type
{
    /**
     * @var class-string
     */
    private string $enumname;

    /**
     * @psalm-mutation-free
     *
     * @param class-string $enumname
     */
    public function __construct(string $enumname)
    {
        $this->enumname = $enumname;
    }

    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return is_string($value) && $value !== '' && (new ReflectionEnum($this->enumname))->hasCase($value);
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    #[Override]
    public function coerce(mixed $value): string
    {
        if (is_string($value) && $value !== '' && (new ReflectionEnum($this->enumname))->hasCase($value)) {
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
        if (is_string($value) && $value !== '' && (new ReflectionEnum($this->enumname))->hasCase($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'enum-case-of<' . $this->enumname . '>';
    }
}
