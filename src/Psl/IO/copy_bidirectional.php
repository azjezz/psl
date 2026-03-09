<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async;
use Psl\DateTime\Duration;

/**
 * Copy data bidirectionally between two handles until both sides reach EOF.
 *
 * This is useful for building proxies: data flows from $a to $b and from $b to $a
 * concurrently until both directions reach EOF.
 *
 * @return array{int<0, max>, int<0, max>} [bytes_a_to_b, bytes_b_to_a]
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws Exception\TimeoutException If the operation times out.
 */
function copy_bidirectional(
    ReadHandleInterface&WriteHandleInterface $a,
    ReadHandleInterface&WriteHandleInterface $b,
    null|Duration $timeout = null,
): array {
    $timer = new Async\OptionalIncrementalTimeout($timeout, static function (): never {
        throw new Exception\TimeoutException('Bidirectional copy operation timed out.');
    });

    /**
     * @var array{int<0, max>, int<0, max>}
     */
    return Async\concurrently([
        static fn(): int => copy($a, $b, $timer->getRemaining()),
        static fn(): int => copy($b, $a, $timer->getRemaining()),
    ]);
}
