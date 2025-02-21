<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\Default\DefaultInterface;

/**
 * Encapsulates socket options for network operations.
 *
 * @psalm-immutable
 */
final readonly class SocketOptions implements DefaultInterface
{
    public bool $addressReuse;
    public bool $portReuse;
    public bool $broadcast;

    /**
     * Initializes a new instance of SocketOptions with the specified settings.
     *
     * @param bool $address_reuse Enables or disables the SO_REUSEADDR socket option.
     * @param bool $port_reuse Enables or disables the SO_REUSEPORT socket option.
     * @param bool $broadcast Enables or disables the SO_BROADCAST socket option.
     *
     * @psalm-mutation-free
     */
    public function __construct(bool $address_reuse, bool $port_reuse, bool $broadcast)
    {
        $this->addressReuse = $address_reuse;
        $this->portReuse = $port_reuse;
        $this->broadcast = $broadcast;
    }

    /**
     * Constructs a new SocketOptions instance with the specified settings.
     *
     * This static factory method facilitates the creation of SocketOptions instances,
     * allowing for explicit and readable configuration of socket behavior at instantiation.
     *
     * @param bool $address_reuse Determines the SO_REUSEADDR socket option state.
     * @param bool $port_reuse Determines the SO_REUSEPORT socket option state.
     * @param bool $broadcast Determines the SO_BROADCAST socket option state.
     *
     * @pure
     */
    public static function create(
        bool $address_reuse = false,
        bool $port_reuse = false,
        bool $broadcast = false,
    ): SocketOptions {
        return new self($address_reuse, $port_reuse, $broadcast);
    }

    /**
     * Provides a default set of socket options.
     *
     * The default instance has all options disabled, creating a conservative and
     * safe starting point for socket configuration.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::create();
    }

    /**
     * Returns a new instance with the address reuse option modified.
     *
     * @param bool $enabled The desired state for the SO_REUSEADDR option.
     *
     * @psalm-mutation-free
     */
    public function withAddressReuse(bool $enabled = true): SocketOptions
    {
        return new self($enabled, $this->portReuse, $this->broadcast);
    }

    /**
     * Returns a new instance with the port reuse option modified.
     *
     * @param bool $enabled The desired state for the SO_REUSEPORT option.
     *
     * @psalm-mutation-free
     */
    public function withPortReuse(bool $enabled = true): SocketOptions
    {
        return new self($this->addressReuse, $enabled, $this->broadcast);
    }

    /**
     * Returns a new instance with the broadcast option modified.
     *
     * @param bool $enabled The desired state for the SO_BROADCAST option.
     *
     * @psalm-mutation-free
     */
    public function withBroadcast(bool $enabled = true): SocketOptions
    {
        return new self($this->addressReuse, $this->portReuse, $enabled);
    }
}
