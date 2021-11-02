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
    public function __construct(?int $timeout_ms, callable $handler)
    {
        $this->handler = $handler;
        if ($timeout_ms === null) {
            $this->end = null;
            return;
        }

        $this->end = microtime(true) + (float) $timeout_ms;
    }

    public function getRemaining(): ?int
    {
        if ($this->end === null) {
            return null;
        }

        $remaining =  $this->end - microtime(true);
        if ($remaining <= 0) {
            $th = $this->handler;
            return $th();
        }

        return $remaining < 1.0 ? 1 : (int) $remaining;
    }
}
