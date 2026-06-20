<?php

declare(strict_types=1);

namespace Psl\Type\Tests\StaticAnalysis;

use Psl\Type;

/**
 * @psalm-pure
 *
 * @return Type\TypeInterface<string>
 */
function tests_purity(): Type\TypeInterface<string>
{
    return Type\converted::<int, string>(Type\int(), Type\string(), static fn(int $value): string => (string) $value);
}
