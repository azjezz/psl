<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @api
 */
interface Equable<T>
{
    public function equals(T $other): bool;
}
