<?php

declare(strict_types=1);

namespace Psl\H2\Exception;

/**
 * Thrown when an HTTP/2 flow control limit is violated.
 *
 * HTTP/2 uses flow control windows to regulate how much DATA a sender
 * may transmit before receiving a WINDOW_UPDATE from the receiver.
 * This exception is raised when those limits are breached or when
 * concurrency caps are exceeded.
 */
final class FlowControlException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create a flow control exception when a sender attempts to write
     * more DATA bytes than the receiver's flow control window allows.
     *
     * The caller should wait for a WINDOW_UPDATE frame before retrying.
     *
     * @param int $streamId The stream whose window is exhausted (0 for the connection window).
     * @param int $available The number of bytes remaining in the window.
     * @param int $requested The number of bytes the sender attempted to write.
     */
    public static function forWindowExhausted(int $streamId, int $available, int $requested): self
    {
        return new self(
            'Flow control window exhausted for stream '
            . $streamId
            . ': available '
            . $available
            . ', requested '
            . $requested
            . '.',
        );
    }

    /**
     * Create a flow control exception when a WINDOW_UPDATE increment
     * would cause the flow control window to exceed the maximum size
     * of 2^31 - 1 bytes (RFC 9113, Section 6.9.1).
     *
     * This is a connection-level error that results in a GOAWAY frame
     * with a FLOW_CONTROL_ERROR code.
     *
     * @param int $streamId The affected stream (0 for the connection window).
     * @param int $currentSize The current window size in bytes.
     * @param int $increment The WINDOW_UPDATE increment that caused the overflow.
     */
    public static function forWindowOverflow(int $streamId, int $currentSize, int $increment): self
    {
        return new self(
            'Flow control window overflow for stream '
            . $streamId
            . ': current '
            . $currentSize
            . ' + increment '
            . $increment
            . ' exceeds maximum.',
        );
    }

    /**
     * Create a flow control exception when the local endpoint attempts
     * to open a new stream but the peer's SETTINGS_MAX_CONCURRENT_STREAMS
     * limit has already been reached.
     *
     * The caller should wait for an existing stream to close before
     * initiating a new one.
     *
     * @param int $max The maximum number of concurrent streams allowed by the peer.
     */
    public static function forMaxConcurrentStreamsExceeded(int $max): self
    {
        return new self('Maximum concurrent streams exceeded: limit is ' . $max . '.');
    }
}
