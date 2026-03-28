<?php

declare(strict_types=1);

namespace Psl\Comparison;

/**
 * @template T
 *
 * @api
 */
interface Equable
{
    /**
     * @param T $other
     */
    public function equals(mixed $other): bool;
}
