<?php

declare(strict_types=1);

namespace Psl\Ansi;

use Stringable;

/**
 * @mutation-free
 */
interface CommandInterface extends Stringable
{
    /**
     * @mutation-free
     */
    public function toString(): string;
}
