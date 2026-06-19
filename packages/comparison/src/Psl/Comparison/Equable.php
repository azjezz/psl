<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @api
 */
interface Equable<T = mixed>
{
    /**
     * @param T $other
     */
    public function equals(mixed $other): bool;
}
