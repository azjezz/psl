<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type\Asset;

use Stringable;

final class ExplicitStringableObject implements Stringable
{
    public function __toString(): string
    {
        return '123';
    }
}
