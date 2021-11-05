<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

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
     * @var (callable(): ?int)
     */
    private $handler;

    /**
     * @param (callable(): ?int) $handler
     */
    public function __construct(?float $timeout, callable $handler)
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
