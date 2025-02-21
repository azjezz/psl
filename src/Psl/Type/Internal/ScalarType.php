<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

/**
 * @extends UnionType<string|bool, int|float>
 *
 * @internal
 */
final readonly class ScalarType extends UnionType
{
    /**
     * @psalm-mutation-free
     */
    public function __construct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        parent::__construct(
            /** @psalm-suppress MissingThrowsDocblock */
            new UnionType(new StringType(), new BoolType()),
            new NumType(),
        );
    }

    #[\Override]
    public function toString(): string
    {
        return 'scalar';
    }
}
