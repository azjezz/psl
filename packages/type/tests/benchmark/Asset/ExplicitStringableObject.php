<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Benchmark\Asset;

use Override;
use Stringable;

final class ExplicitStringableObject implements Stringable
{
    #[Override]
    public function __toString(): string
    {
        return '123';
    }
}
