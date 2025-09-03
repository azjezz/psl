<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;

/**
 * @extends UnionType<int, float>
 *
 * @internal
 */
final readonly class NumType extends UnionType
{
    /**
     * @psalm-mutation-free
     */
    public function __construct()
    {
        parent::__construct(new IntType(), new FloatType());
    }

    #[Override]
    public function toString(): string
    {
        return 'num';
    }
}
