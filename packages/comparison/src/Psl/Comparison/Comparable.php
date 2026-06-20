<?php

declare(strict_types=1);

namespace Psl\Comparison;

use Psl\Comparison\Exception\IncomparableException;

/**
 * @api
 */
interface Comparable<T>
{
    /**
     * @param T $other
     *
     * @optionallyThrows IncomparableException - In case you want to bail out on specific comparisons.
     */
    public function compare(mixed $other): Order;
}
