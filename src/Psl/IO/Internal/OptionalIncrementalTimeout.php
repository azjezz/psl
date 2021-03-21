<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\Asio;

final class OptionalIncrementalTimeout
{
    private ?int $end;
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

        $this->end = Asio\time() + $timeout_ms;
    }

    public function getRemaining(): ?int
    {
        if ($this->end === null) {
            return null;
        }

        $remaining = $this->end - Asio\time();
        if ($remaining <= 0) {
            $th = $this->handler;
            return $th();
        }
        return $remaining;
    }
}
