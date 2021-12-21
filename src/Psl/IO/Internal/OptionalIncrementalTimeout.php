<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Closure;

use function microtime;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class OptionalIncrementalTimeout
{
    private ?float $end;
    /**
     * @var (Closure(): ?int)
     */
    private $handler;

    /**
     * @param (Closure(): ?int) $handler
     */
    public function __construct(?float $timeout, Closure $handler)
    {
        $this->handler = $handler;
        if ($timeout === null) {
            $this->end = null;
            return;
        }

        $this->end = microtime(true) + $timeout;
    }

    public function getRemaining(): ?float
    {
        if ($this->end === null) {
            return null;
        }

        $remaining =  $this->end - microtime(true);
        if ($remaining <= 0) {
            $th = $this->handler;
            return $th();
        }

        return $remaining;
    }
}
