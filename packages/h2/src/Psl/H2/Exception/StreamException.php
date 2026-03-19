<?php

declare(strict_types=1);

namespace Psl\H2\Exception;

/**
 * Thrown when an operation targets an HTTP/2 stream that is in an
 * unexpected or unusable state.
 *
 * Each factory method corresponds to a specific stream-level failure
 * condition defined by RFC 9113.
 */
final class StreamException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create a stream exception when a frame is received for a stream
     * that is not in the expected state for that frame type.
     *
     * This is thrown, for example, when a DATA frame arrives on a
     * half-closed (remote) or idle stream.
     *
     * @param int $streamId The stream that is in the wrong state.
     * @param string $expected The state the stream was expected to be in.
     * @param string $actual The state the stream is actually in.
     */
    public static function forInvalidState(int $streamId, string $expected, string $actual): self
    {
        return new self('Stream ' . $streamId . ' is in state ' . $actual . ', expected ' . $expected . '.');
    }

    /**
     * Create a stream exception when an operation is attempted on a
     * stream that has already been fully closed.
     *
     * @param int $streamId The closed stream identifier.
     */
    public static function forStreamClosed(int $streamId): self
    {
        return new self('Stream ' . $streamId . ' is closed.');
    }

    /**
     * Create a stream exception when the remote peer refuses a newly
     * initiated stream via a RST_STREAM with REFUSED_STREAM.
     *
     * The request that opened this stream was never processed and may
     * be safely retried on a new stream.
     *
     * @param int $streamId The refused stream identifier.
     */
    public static function forStreamRefused(int $streamId): self
    {
        return new self('Stream ' . $streamId . ' was refused.');
    }

    /**
     * Create a stream exception when a stream is terminated by the
     * remote peer via a RST_STREAM frame.
     *
     * @param int $streamId The reset stream identifier.
     * @param int $errorCode The HTTP/2 error code received in the RST_STREAM frame.
     */
    public static function forStreamReset(int $streamId, int $errorCode): self
    {
        return new self('Stream ' . $streamId . ' was reset with error code ' . $errorCode . '.');
    }
}
