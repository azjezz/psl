<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;

/**
 * @internal
 */
final readonly class ScalarType extends UnionType<string|bool, int|float>
{
    /**
     * @psalm-mutation-free
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct(new UnionType::<string, bool>(new StringType(), new BoolType()), new NumType());
    }

    #[Override]
    public function toString(): string
    {
        return 'scalar';
    }
}
