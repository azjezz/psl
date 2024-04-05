<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

/**
 * @extends UnionType<int, float>
 *
 * @internal
 */
final class NumType extends UnionType
{
    /**
     * @psalm-mutation-free
     */
    public function __construct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        parent::__construct(new IntType(), new FloatType());
    }

    public function toString(): string
    {
        return 'num';
    }
}
