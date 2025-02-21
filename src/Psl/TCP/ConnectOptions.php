<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Default\DefaultInterface;

/**
 * Represents the configuration options for TCP connections.
 *
 * @psalm-immutable
 */
final readonly class ConnectOptions implements DefaultInterface
{
    public bool $noDelay;

    /**
     * Initializes a new instance of {@see ConnectOptions} with the specified settings.
     *
     * @param bool $no_delay Determines whether the TCP_NODELAY option is enabled, controlling
     *                       the use of the Nagle algorithm. When true, TCP_NODELAY is enabled,
     *                       and the Nagle algorithm is disabled.
     *
     * @psalm-mutation-free
     */
    public function __construct(bool $no_delay)
    {
        $this->noDelay = $no_delay;
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
    public static function create(bool $no_delay = false): ConnectOptions
    {
        return new self($no_delay);
    }

    /**
     * Creates and returns a default instance of {@see ConnectOptions}.
     *
     * The default instance has the TCP_NODELAY option disabled, allowing the Nagle algorithm
     * to be used. This method is a convenience wrapper around the `create` method, adhering to
     * the {@see DefaultInterface} contract.
     *
     * @return static A default ConnectOptions instance with noDelay set to false.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::create();
    }

    /**
     * Returns a new instance of {@see ConnectOptions} with the noDelay setting modified.
     *
     * @param bool $enabled Specifies the desired state of the TCP_NODELAY option.
     *
     * @psalm-mutation-free
     */
    public function withNoDelay(bool $enabled = true): ConnectOptions
    {
        return new self($enabled);
    }
}
