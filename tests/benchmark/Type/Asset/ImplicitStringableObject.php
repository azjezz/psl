<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type\Asset;

final class ImplicitStringableObject
{
    #[\Override]
    public function __toString(): string
    {
        return '123';
    }
}
