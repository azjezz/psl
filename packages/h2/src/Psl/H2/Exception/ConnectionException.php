<?php

declare(strict_types=1);

namespace Psl\H2\Exception;

/**
 * Thrown when an operation is attempted on an HTTP/2 connection that
 * is no longer usable.
 */
final class ConnectionException extends RuntimeException
{
    /**
     * Create a connection exception when the HTTP/2 connection has
     * been closed (either gracefully via GOAWAY or due to a transport
     * error) and no further streams or frames can be sent or received.
     */
    public static function forConnectionClosed(): self
    {
        return new self('HTTP/2 connection is closed.');
    }
}
