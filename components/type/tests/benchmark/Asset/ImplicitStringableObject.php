<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Benchmark\Asset;

use Override;

final class ImplicitStringableObject
{
    #[Override]
    public function __toString(): string
    {
        return '123';
    }
}
