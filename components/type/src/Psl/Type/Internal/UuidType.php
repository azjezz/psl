<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Stringable;

use function is_string;
use function preg_match;

/**
 * @extends Type\Type<non-empty-string>
 *
 * @internal
 */
final readonly class UuidType extends Type\Type
{
    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-string
     */
    #[Override]
    public function coerce(mixed $value): string
    {
        /** @mago-expect analysis:mixed-assignment */
        $string = $value instanceof Stringable ? (string) $value : $value;

        if ($this->matches($string)) {
            return $string;
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
        if ($this->matches($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'uuid';
    }
}
