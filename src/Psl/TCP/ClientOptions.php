<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Default\DefaultInterface;

/**
 * Represents the configuration options for TCP connections.
 *
 * @immutable
 */
final readonly class ClientOptions implements DefaultInterface
{
    /**
     * Indicates whether to disable Nagle's algorithm.
     *
     * @var bool
     */
    public bool $noDelay;

    /**
     * The IP address and optionally the port to bind to.
     *
     * @var null|array{non-empty-string, null|int}
     */
    public ?array $bindTo;

    /**
     * The TLS client options.
     *
     * @var TLS\ClientOptions|null
     */
    public ?TLS\ClientOptions $tlsClientOptions;

    /**
     * Constructor is private to enforce immutability. Use static creation methods instead.
     *
     * @param bool $no_delay Indicates whether to disable Nagle's algorithm. When true, packets are sent immediately.
     * @param null|array{non-empty-string, null|int} $bind_to Specifies the IP address and optionally the port to bind to.
     *                                                        Format: [`IP`, `Port`]. `Port` is optional and can be null.
     * @param TLS\ClientOptions|null $tls_client_options The TLS client options.
     *
     * @pure
     *
     * @psalm-suppress ImpureVariable
     */
    private function __construct(bool $no_delay, ?array $bind_to, ?TLS\ClientOptions $tls_client_options)
    {
        $this->noDelay = $no_delay;
        $this->bindTo = $bind_to;
        $this->tlsClientOptions = $tls_client_options;
    }

    /**
     * Constructs a new ConnectOptions instance with specified noDelay setting.
     *
     * This static method provides a named constructor pattern, allowing for explicit
     * configuration of the options at the time of instantiation. It offers an alternative
     * to the default constructor for cases where named constructors improve readability
     * and usage clarity.
     *
     * @param bool $no_delay Specifies whether the TCP_NODELAY option should be enabled.
     *
     * @pure
     */
    public static function create(bool $no_delay = false): ClientOptions
    {
        return new self($no_delay, null, null);
    }

    /**
     * Creates and returns a default instance of {@see ClientOptions}.
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
     * @return ClientOptions A new instance with noDelay set to true.
     *
     * @mutation-free
     */
    public function withNoDelay(bool $enabled = true): ClientOptions
    {
        return new self($enabled, $this->bindTo, $this->tlsClientOptions);
    }

    /**
     * Returns a new instance with the specified IP and optionally port to bind to.
     *
     * @param non-empty-string $ip The IP address to bind the connection to.
     * @param int|null $port The port number to bind the connection to, or null to not specify.
     *
     * @return ClientOptions A new instance with the updated bindTo option.
     *
     * @mutation-free
     */
    public function withBindTo(string $ip, ?int $port = null): ClientOptions
    {
        return new self($this->noDelay, [$ip, $port], $this->tlsClientOptions);
    }

    /**
     * Returns a new instance without any bindTo configuration.
     *
     * @return ClientOptions A new instance with bindTo set to null.
     *
     * @mutation-free
     */
    public function withoutBindTo(): ClientOptions
    {
        return new self($this->noDelay, null, $this->tlsClientOptions);
    }

    /**
     * Returns a new instance with the specified TLS client options.
     *
     * @param TLS\ClientOptions $tls_connect_options The TLS connect options.
     *
     * @mutation-free
     */
    public function withTlsClientOptions(TLS\ClientOptions $tls_connect_options): ClientOptions
    {
        return new self($this->noDelay, $this->bindTo, $tls_connect_options);
    }

    /**
     * Returns a new instance without the Tls client options.
     *
     * @mutation-free
     */
    public function withoutTlsClientOptions(): ClientOptions
    {
        return new self($this->noDelay, $this->bindTo, null);
    }
}
