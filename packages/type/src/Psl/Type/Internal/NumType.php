<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;

/**
 * @internal
 */
final readonly class NumType extends UnionType<int, float>
{
    /**
     * @psalm-mutation-free
     *
     * @codeCoverageIgnore
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
