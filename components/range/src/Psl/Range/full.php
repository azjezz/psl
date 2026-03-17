<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 */
function full(): FullRange
{
    return new FullRange();
}
