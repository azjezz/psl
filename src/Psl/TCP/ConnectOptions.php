<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Default\DefaultInterface;

/**
 * Represents the configuration options for TCP connections.
 *
 * @immutable
 */
final class ConnectOptions implements DefaultInterface
{
    /**
     * Constructor is private to enforce immutability. Use static creation methods instead.
     *
     * @param bool $noDelay Indicates whether to disable Nagle's algorithm. When true, packets are sent immediately.
     * @param null|array{non-empty-string, null|int} $bindTo Specifies the IP address and optionally the port to bind to.
     *                                                       Format: [`IP`, `Port`]. `Port` is optional and can be null.
     *
     * @pure
     */
    private function __construct(
        public readonly bool                $noDelay,
        public readonly ?array              $bindTo,
        public readonly ?TLS\ConnectOptions $TLSConnectOptions,
    ) {
    }

    /**
     * Constructs a new ConnectOptions instance with specified noDelay setting.
     *
     * This static method provides a named constructor pattern, allowing for explicit
     * configuration of the options at the time of instantiation. It offers an alternative
     * to the default constructor for cases where named constructors improve readability
     * and usage clarity.
     *
     * @param bool $noDelay Specifies whether the TCP_NODELAY option should be enabled.
     *
     * @pure
     */
    public static function create(bool $noDelay = false): ConnectOptions
    {
        return new self($noDelay, null, null);
    }

    /**
     * Creates and returns a default instance of {@see ConnectOptions}.
     *
     * @pure
     */
    public static function default(): static
    {
        return self::create();
    }

    /**
     * Returns a new instance with noDelay enabled.
     *
     * @return ConnectOptions A new instance with noDelay set to true.
     *
     * @mutation-free
     */
    public function withNoDelay(bool $enabled = true): ConnectOptions
    {
        return new self($enabled, $this->bindTo, $this->TLSConnectOptions);
    }

    /**
     * Returns a new instance with the specified IP and optionally port to bind to.
     *
     * @param non-empty-string $ip The IP address to bind the connection to.
     * @param int|null $port The port number to bind the connection to, or null to not specify.
     *
     * @return ConnectOptions A new instance with the updated bindTo option.
     *
     * @mutation-free
     */
    public function withBindTo(string $ip, ?int $port = null): ConnectOptions
    {
        return new self($this->noDelay, [$ip, $port], $this->TLSConnectOptions);
    }

    /**
     * Returns a new instance without any bindTo configuration.
     *
     * @return ConnectOptions A new instance with bindTo set to null.
     *
     * @mutation-free
     */
    public function withoutBindTo(): ConnectOptions
    {
        return new self($this->noDelay, null, $this->TLSConnectOptions);
    }

    /**
     * Returns a new instance with the specified TLS connect options.
     *
     * @param TLS\ConnectOptions $tls_connect_options The TLS connect options.
     *
     * @mutation-free
     */
    public function withTLSConnectOptions(TLS\ConnectOptions $tls_connect_options): ConnectOptions
    {
        return new self($this->noDelay, $this->bindTo, $tls_connect_options);
    }

    /**
     * Returns a new instance without the Tls connect options.
     *
     * @mutation-free
     */
    public function withoutTlsConnectOptions(): ConnectOptions
    {
        return new self($this->noDelay, $this->bindTo, null);
    }
}
