<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
use Psl\Network;

/**
 * A connected TCP stream with TCP-specific socket options.
 */
interface StreamInterface extends Network\StreamInterface
{
    /**
     * Enable or disable TCP_NODELAY (disables Nagle's algorithm).
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setNoDelay(bool $enabled): void;

    /**
     * Get the current TCP_NODELAY setting.
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getNoDelay(): bool;

    /**
     * Set the IP Time-To-Live value.
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setTtl(int $ttl): void;

    /**
     * Get the current IP Time-To-Live value.
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getTtl(): int;

    /**
     * Enable or disable TCP keep-alive.
     *
     * When enabled, the operating system periodically sends probes on idle connections
     * to detect if the remote peer is still reachable.
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setKeepAlive(bool $enabled): void;

    /**
     * Get the current TCP keep-alive setting.
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getKeepAlive(): bool;

    /**
     * Set the send buffer size in bytes.
     *
     * @param int<1, max> $size
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setSendBufferSize(int $size): void;

    /**
     * Get the current send buffer size in bytes.
     *
     * @return int<1, max>
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getSendBufferSize(): int;

    /**
     * Set the receive buffer size in bytes.
     *
     * @param int<1, max> $size
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setReceiveBufferSize(int $size): void;

    /**
     * Get the current receive buffer size in bytes.
     *
     * @return int<1, max>
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getReceiveBufferSize(): int;

    /**
     * Set the linger behavior on close.
     *
     * When null, the default OS behavior is used (graceful close, FIN sent).
     * When Duration::zero(), the connection is reset immediately on close (RST sent).
     * When a positive duration, close blocks for up to that duration waiting for
     * queued data to be sent before resetting the connection.
     *
     * @throws Network\Exception\RuntimeException If unable to set the option.
     */
    public function setLinger(null|Duration $duration): void;

    /**
     * Get the current linger setting.
     *
     * Returns null if linger is disabled, or the linger duration if enabled.
     *
     * @throws Network\Exception\RuntimeException If unable to get the option.
     */
    public function getLinger(): null|Duration;
}
