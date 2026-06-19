<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface ToIntl<T = mixed>
{
    /**
     * @return T
     */
    public function toIntl(): mixed;
}
