<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use function max;
use function min;

/**
 * Estimates Bandwidth-Delay Product (BDP) to dynamically size H2 receive windows.
 *
 * Uses PING round-trip times and data throughput measurements to calculate
 * optimal window sizes. Applies EWMA smoothing to both RTT and throughput
 * for stability.
 *
 * @internal
 */
final class BDPEstimator
{
    private const float EWMA_ALPHA = 0.3;

    private const float CONNECTION_THRESHOLD = 0.5;

    private const float STREAM_THRESHOLD = 0.5;

    /** @var float EWMA-smoothed round-trip time in seconds. */
    private float $smoothedRtt = 0.0;

    /** @var float EWMA-smoothed throughput in bytes per second. */
    private float $smoothedThroughput = 0.0;

    /** @var int Total bytes received since the last PING was sent. */
    private int $bytesReceivedSinceLastPing = 0;

    /** @var float Timestamp (in seconds) when the last PING was sent. */
    private float $lastPingSentAt = 0.0;

    /** @var bool Whether a PING is currently awaiting an ACK. */
    private bool $pingInFlight = false;

    /** @var int Connection-level bytes consumed since the last WINDOW_UPDATE. */
    private int $connectionBytesConsumed = 0;

    /** @var array<int, int> Per-stream bytes consumed since the last WINDOW_UPDATE, keyed by stream ID. */
    private array $streamBytesConsumed = [];

    /** @var int Current target connection receive window size in bytes. */
    private int $targetConnectionWindow;

    /**
     * @param int $initialWindowSize Initial flow control window size (used as floor).
     * @param int $maxReceiveWindowSize Maximum allowed window size (cap).
     */
    public function __construct(
        private readonly int $initialWindowSize,
        private readonly int $maxReceiveWindowSize,
    ) {
        $this->targetConnectionWindow = $initialWindowSize;
    }

    /**
     * Record that a PING frame was sent at the given timestamp.
     *
     * @param float $timestamp Monotonic timestamp in seconds.
     */
    public function onPingSent(float $timestamp): void
    {
        $this->lastPingSentAt = $timestamp;
        $this->bytesReceivedSinceLastPing = 0;
        $this->pingInFlight = true;
    }

    /**
     * @return null|int Connection window growth increment, or null if no growth needed.
     */
    public function onPingAck(float $timestamp): null|int
    {
        if (!$this->pingInFlight) {
            return null;
        }

        $this->pingInFlight = false;

        $rtt = $timestamp - $this->lastPingSentAt;
        if ($rtt <= 0.0) {
            return null;
        }

        if ($this->smoothedRtt === 0.0) {
            $this->smoothedRtt = $rtt;
        } else {
            $this->smoothedRtt = (self::EWMA_ALPHA * $rtt) + ((1.0 - self::EWMA_ALPHA) * $this->smoothedRtt);
        }

        $throughput = $this->bytesReceivedSinceLastPing / $rtt;
        if ($this->smoothedThroughput === 0.0) {
            $this->smoothedThroughput = $throughput;
        } else {
            $this->smoothedThroughput =
                (self::EWMA_ALPHA * $throughput) + ((1.0 - self::EWMA_ALPHA) * $this->smoothedThroughput);
        }

        $bdp = $this->smoothedThroughput * $this->smoothedRtt;
        $newTarget = (int) max($this->initialWindowSize, 2.0 * $bdp);
        $newTarget = min($newTarget, $this->maxReceiveWindowSize);

        $oldTarget = $this->targetConnectionWindow;
        $this->targetConnectionWindow = $newTarget;

        if ($newTarget > $oldTarget) {
            return $newTarget - $oldTarget;
        }

        return null;
    }

    /**
     * Record received data and return WINDOW_UPDATE increments when thresholds are reached.
     *
     * @return list<array{non-negative-int, positive-int}> List of [streamId, increment] pairs for WINDOW_UPDATEs.
     *         Stream ID 0 means connection-level update.
     */
    public function recordDataReceived(int $streamId, int $bytes): array
    {
        $this->bytesReceivedSinceLastPing += $bytes;
        $this->connectionBytesConsumed += $bytes;
        $this->streamBytesConsumed[$streamId] = ($this->streamBytesConsumed[$streamId] ?? 0) + $bytes;

        /** @var list<array{non-negative-int, positive-int}> $updates */
        $updates = [];

        $connectionThreshold = (int) ($this->targetConnectionWindow * self::CONNECTION_THRESHOLD);
        if ($this->connectionBytesConsumed >= $connectionThreshold) {
            /** @var positive-int $connectionIncrement */
            $connectionIncrement = $this->connectionBytesConsumed;
            $this->connectionBytesConsumed = 0;
            /** @var non-negative-int $connectionStreamId */
            $connectionStreamId = 0;
            $updates[] = [$connectionStreamId, $connectionIncrement];
        }

        $streamThreshold = (int) ($this->initialWindowSize * self::STREAM_THRESHOLD);
        if ($this->streamBytesConsumed[$streamId] >= $streamThreshold) {
            /** @var positive-int $streamIncrement */
            $streamIncrement = $this->streamBytesConsumed[$streamId];
            $this->streamBytesConsumed[$streamId] = 0;
            /** @var non-negative-int $streamId */
            $updates[] = [$streamId, $streamIncrement];
        }

        return $updates;
    }

    /**
     * Remove per-stream tracking data when a stream is closed.
     *
     * @param int $streamId The stream ID to remove.
     */
    public function removeStream(int $streamId): void
    {
        unset($this->streamBytesConsumed[$streamId]);
    }
}
