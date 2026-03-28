<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @template T
 *
 * @api
 */
interface ToIntl
{
    /**
     * @return T
     */
    public function toIntl(): mixed;
}
