<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Default\DefaultInterface;
use Psl\Network;

/**
 * Configures options for a TCP server.
 *
 * @psalm-immutable
 */
final readonly class ServerOptions implements DefaultInterface
{
    /**
     * Default number of idle connections allowed.
     */
    public const DEFAULT_IDLE_CONNECTIONS = 256;

    public bool $noDelay;

    /**
     * @var int<1, max>
     */
    public int $idleConnections;
    public Network\SocketOptions $socketOptions;

    /**
     * Initializes a new instance of ServerOptions with specified settings.
     *
     * @param bool $no_delay Determines whether the TCP_NODELAY option is enabled, controlling
     *                       the use of the Nagle algorithm. When true, TCP_NODELAY is enabled,
     *                       and the Nagle algorithm is disabled.
     * @param int<1, max> $idle_connections The maximum number of idle connections the server will keep open.
     * @param Network\SocketOptions $socket_options Socket configuration options.
     *
     * @psalm-mutation-free
     */
    public function __construct(bool $no_delay, int $idle_connections, Network\SocketOptions $socket_options)
    {
        $this->noDelay = $no_delay;
        $this->idleConnections = $idle_connections;
        $this->socketOptions = $socket_options;
    }

    /**
     * Creates a new {@see ServerOptions} instance with the specified settings.
     *
     * This method provides a convenient way to create a {@see ServerOptions} instance with custom settings
     * or with defaults.
     *
     * @param bool $no_delay Specifies whether the TCP_NODELAY option should be enabled.
     * @param int<1, max> $idle_connections Specifies the maximum number of idle connections to allow.
     * @param ?Network\SocketOptions $socket_options Optional. Specifies custom socket options. If null, defaults are used.
     *
     * @pure
     */
    public static function create(
        bool $no_delay = false,
        int $idle_connections = self::DEFAULT_IDLE_CONNECTIONS,
        null|Network\SocketOptions $socket_options = null,
    ): ServerOptions {
        return new self($no_delay, $idle_connections, $socket_options ?? Network\SocketOptions::default());
    }

    /**
     * Provides a default {@see ServerOptions} instance.
     *
     * Returns a {@see ServerOptions} instance configured with default settings. This includes TCP_NODELAY disabled,
     * the default number of idle connections, and default socket options.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::create();
    }

    /**
     * Returns a new instance with updated socket options.
     *
     * @param Network\SocketOptions $socket_options New socket configuration options.
     *
     * @psalm-mutation-free
     */
    public function withSocketOptions(Network\SocketOptions $socket_options): ServerOptions
    {
        return new self($this->noDelay, $this->idleConnections, $socket_options);
    }

    /**
     * Returns a new instance with the noDelay option modified.
     *
     * @param bool $enabled The desired state for the TCP_NODELAY option.
     *
     * @psalm-mutation-free
     */
    public function withNoDelay(bool $enabled = true): ServerOptions
    {
        return new self($enabled, $this->idleConnections, $this->socketOptions);
    }

    /**
     * Returns a new instance with the idleConnections option modified.
     *
     * @param int<1, max> $idleConnections The new maximum number of idle connections to allow.
     *
     * @psalm-mutation-free
     */
    public function withIdleConnections(int $idleConnections): ServerOptions
    {
        return new self($this->noDelay, $idleConnections, $this->socketOptions);
    }
}
