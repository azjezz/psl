<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\Default\DefaultInterface;

/**
 * Encapsulates socket options for network operations.
 *
 * @immutable
 */
final class SocketOptions implements DefaultInterface
{
    /**
     * Initializes a new instance of SocketOptions with the specified settings.
     *
     * @param bool $addressReuse Enables or disables the SO_REUSEADDR socket option.
     * @param bool $portReuse Enables or disables the SO_REUSEPORT socket option.
     * @param bool $broadcast Enables or disables the SO_BROADCAST socket option.
     *
     * @pure
     */
    public function __construct(
        public readonly bool $addressReuse,
        public readonly bool $portReuse,
        public readonly bool $broadcast,
    ) {
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
    public static function create(bool $address_reuse = false, bool $port_reuse = false, bool $broadcast = false): SocketOptions
    {
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
    public static function default(): static
    {
        return self::create();
    }

    /**
     * Returns a new instance with the address reuse option modified.
     *
     * @param bool $enabled The desired state for the SO_REUSEADDR option.
     *
     * @mutation-free
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
     * @mutation-free
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
     * @mutation-free
     */
    public function withBroadcast(bool $enabled = true): SocketOptions
    {
        return new self($this->addressReuse, $this->portReuse, $enabled);
    }
}
