<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

/**
 * @extends UnionType<string|bool, int|float>
 *
 * @internal
 */
final class ScalarType extends UnionType
{
    public function __construct()
    {
        parent::__construct(
            new UnionType(new StringType(), new BoolType()),
            new NumType()
        );
    }

    public function toString(): string
    {
        return 'scalar';
    }
}
