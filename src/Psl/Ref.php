<?php

declare(strict_types=1);

namespace Psl;

/**
 * Wrapper class for getting object (byref) semantics for a value type.
 *
 * This is especially useful for mutating values outside a lambda's scope.
 *
 * In general, it's preferable to refactor to use return values.
 *
 * `Iter\reduce()` can also be used in some situations to avoid this class.
 *
 * @template T
 */
final class Ref
{
    /**
     * @var T
     */
    public mixed $value;

    /**
     * @param T $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
}
