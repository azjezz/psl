<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use JsonException;
use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
final readonly class JsonDecodedType<T> extends Type\Type<T>
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private Type\TypeInterface<T> $inner,
    ) {}

    /**
     * @throws CoercionException
     */
    #[Override]
    public function coerce(mixed $value): T
    {
        if ($this->inner->matches($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw CoercionException::withValue(
                $value,
                $this->toString(),
                PathExpression::coerceInput($value, $this->inner->toString()),
                $e,
            );
        }

        try {
            return $this->inner->coerce($decoded);
        } catch (Throwable $e) {
            throw CoercionException::withValue(
                $value,
                $this->toString(),
                PathExpression::coerceOutput($decoded, $this->inner->toString()),
                $e,
            );
        }
    }

    /**
     * @throws AssertException
     *
     * @psalm-assert T $value
     */
    #[Override]
    public function assert(mixed $value): T
    {
        return $this->inner->assert($value);
    }

    #[Override]
    public function toString(): string
    {
        return 'json-decoded<' . $this->inner->toString() . '>';
    }
}
