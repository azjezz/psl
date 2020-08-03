<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

/**
 * @extends UnionType<string, int>
 *
 * @internal
 */
final class ArrayKeyType extends UnionType
{
    public function __construct()
    {
        parent::__construct(new StringType(), new IntType());
    }

    public function toString(): string
    {
        return 'array-key';
    }
}
