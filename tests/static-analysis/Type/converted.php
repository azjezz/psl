<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Type;

use Psl\Type;

/**
 * @psalm-pure
 *
 * @return Type\TypeInterface<string>
 */
function tests_purity(): Type\TypeInterface
{
    return Type\converted(Type\int(), Type\string(), static fn(int $value): string => (string) $value);
}
