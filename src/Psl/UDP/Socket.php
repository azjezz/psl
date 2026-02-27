<?php

declare(strict_types=1);

namespace Psl\UDP;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Network;
use Revolt\EventLoop;

use function fclose;
use function is_resource;
use function socket_connect;
use function socket_get_option;
use function socket_import_stream;
use function socket_set_option;
use function str_starts_with;
use function stream_context_create;
use function stream_set_blocking;
use function stream_socket_get_name;
use function stream_socket_recvfrom;
use function stream_socket_sendto;
use function stream_socket_server;
use function strlen;
use function strpos;
use function strrpos;
use function substr;

use const IPPROTO_IP;
use const IPPROTO_IPV6;
use const MCAST_JOIN_GROUP;
use const MCAST_LEAVE_GROUP;
use const PHP_OS_FAMILY;
use const SO_BROADCAST;
use const SOL_SOCKET;
use const STREAM_PEEK;
use const STREAM_SERVER_BIND;

/**
 * A UDP socket for sending and receiving datagrams.
 *
 * Supports both connected and unconnected modes, multicast, broadcast, and peek.
 *
 * @mago-expect lint:cyclomatic-complexity
 */
final class Socket implements IO\CloseHandleInterface, IO\StreamHandleInterface
{
    /**
     * Maximum IPv4 UDP payload size (65535 - 20 IP header - 8 UDP header).
     */
    private const int MAX_DATAGRAM_SIZE = 65_507;

    /**
     * @var resource|closed-resource|null
     */
    private mixed $stream;
    private bool $connected = false;
    private null|Network\Address $peerAddress = null;

    /**
     * @param resource $stream
     */
    private function __construct(mixed $stream)
    {
        $this->stream = $stream;
        stream_set_blocking($stream, false);
    }

    /**
     * Create a UDP socket bound to the given address.
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If failed to create or bind the socket.
     */
    public static function bind(
        string $host = '0.0.0.0',
        int $port = 0,
        bool $reuse_address = false,
        bool $reuse_port = false,
        bool $broadcast = false,
    ): self {
        $context = ['socket' => [
            'so_reuseaddr' => $reuse_address,
            'so_reuseport' => $reuse_port,
            'so_broadcast' => $broadcast,
        ]];

        $ctx = stream_context_create($context);
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_server("udp://{$host}:{$port}", $errno, $errstr, STREAM_SERVER_BIND, $ctx);

        if ($socket === false) {
            throw new Network\Exception\RuntimeException("Failed to bind UDP socket: {$errstr}", (int) $errno);
        }

        return new self($socket);
    }

    /**
     * Connect the socket to a remote address.
     *
     * After connecting, you must use send()/receive() instead of sendTo()/receiveFrom().
     *
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     *
     * @throws Network\Exception\RuntimeException If the connect fails.
     */
    public function connect(string $host, int $port): void
    {
        $stream = $this->getResource();

        $socket = @socket_import_stream($stream);
        if ($socket === false) {
            throw new Network\Exception\RuntimeException('Failed to import stream as socket.');
        }

        if (!@socket_connect($socket, $host, $port)) {
            throw new Network\Exception\RuntimeException("Failed to connect UDP socket to {$host}:{$port}.");
        }

        $this->connected = true;
        $this->peerAddress = Network\Address::udp($host, $port);
    }

    /**
     * Send data to a specific address (unconnected mode).
     *
     * @return int<0, max> Number of bytes sent.
     *
     * @throws Network\Exception\RuntimeException If the send fails or the socket is connected.
     * @throws Network\Exception\InvalidArgumentException If the datagram exceeds the maximum size.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function sendTo(string $data, Network\Address $address, null|Duration $timeout = null): int
    {
        if ($this->connected) {
            throw new Network\Exception\RuntimeException(
                'Cannot use sendTo() on a connected socket. Use send() instead.',
            );
        }

        $this->validatePayloadSize($data);
        $stream = $this->getResource();

        $target = "{$address->host}:{$address->port}";

        if ($timeout !== null) {
            $this->waitWritable($stream, $timeout);
        }

        $result = @stream_socket_sendto($stream, $data, 0, $target);
        if ($result === false || $result === -1) {
            throw new Network\Exception\RuntimeException('Failed to send UDP datagram.');
        }

        /** @var int<0, max> */
        return $result;
    }

    /**
     * Receive data and the sender's address (unconnected mode).
     *
     * @param positive-int $max_bytes
     *
     * @return array{string, Network\Address} [data, sender_address]
     *
     * @throws Network\Exception\RuntimeException If the receive fails or the socket is connected.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function receiveFrom(int $max_bytes, null|Duration $timeout = null): array
    {
        if ($this->connected) {
            throw new Network\Exception\RuntimeException(
                'Cannot use receiveFrom() on a connected socket. Use receive() instead.',
            );
        }

        $stream = $this->getResource();

        $this->awaitReadable($stream, $timeout);

        $address = '';
        $data = @stream_socket_recvfrom($stream, $max_bytes, 0, $address);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to receive UDP datagram.');
        }

        return [$data, $this->parseAddress($address)];
    }

    /**
     * Send data on a connected socket.
     *
     * @return int<0, max> Number of bytes sent.
     *
     * @throws Network\Exception\RuntimeException If the send fails or the socket is not connected.
     * @throws Network\Exception\InvalidArgumentException If the datagram exceeds the maximum size.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function send(string $data, null|Duration $timeout = null): int
    {
        if (!$this->connected) {
            throw new Network\Exception\RuntimeException(
                'Cannot send on an unconnected socket. Use sendTo() or call connect() first.',
            );
        }

        $this->validatePayloadSize($data);
        $stream = $this->getResource();

        if ($timeout !== null) {
            $this->waitWritable($stream, $timeout);
        }

        $result = @stream_socket_sendto($stream, $data);
        if ($result === false || $result === -1) {
            throw new Network\Exception\RuntimeException('Failed to send UDP datagram.');
        }

        /** @var int<0, max> */
        return $result;
    }

    /**
     * Receive data on a connected socket.
     *
     * @param positive-int $max_bytes
     *
     * @throws Network\Exception\RuntimeException If the receive fails or the socket is not connected.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function receive(int $max_bytes, null|Duration $timeout = null): string
    {
        if (!$this->connected) {
            throw new Network\Exception\RuntimeException(
                'Cannot receive on an unconnected socket. Use receiveFrom() or call connect() first.',
            );
        }

        $stream = $this->getResource();

        $this->awaitReadable($stream, $timeout);

        $data = @stream_socket_recvfrom($stream, $max_bytes, 0);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to receive UDP datagram.');
        }

        return $data;
    }

    /**
     * Peek at incoming data without consuming it.
     *
     * @param positive-int $max_bytes
     *
     * @throws Network\Exception\RuntimeException If the peek fails.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function peek(int $max_bytes, null|Duration $timeout = null): string
    {
        $stream = $this->getResource();

        $this->awaitReadable($stream, $timeout);

        $data = @stream_socket_recvfrom($stream, $max_bytes, STREAM_PEEK);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to peek UDP datagram.');
        }

        return $data;
    }

    /**
     * Peek at incoming data and get the sender's address.
     *
     * @param positive-int $max_bytes
     *
     * @return array{string, Network\Address} [data, sender_address]
     *
     * @throws Network\Exception\RuntimeException If the peek fails.
     * @throws IO\Exception\TimeoutException If the operation times out.
     */
    public function peekFrom(int $max_bytes, null|Duration $timeout = null): array
    {
        $stream = $this->getResource();

        $this->awaitReadable($stream, $timeout);

        $address = '';
        $data = @stream_socket_recvfrom($stream, $max_bytes, STREAM_PEEK, $address);
        if ($data === false) {
            throw new Network\Exception\RuntimeException('Failed to peek UDP datagram.');
        }

        return [$data, $this->parseAddress($address)];
    }

    /**
     * Get the local address this socket is bound to.
     *
     * @throws Network\Exception\RuntimeException If unable to retrieve local address.
     */
    public function getLocalAddress(): Network\Address
    {
        $stream = $this->getResource();
        $name = @stream_socket_get_name($stream, false);
        if ($name === false) {
            throw new Network\Exception\RuntimeException('Failed to get local address.');
        }

        return $this->parseAddress($name);
    }

    /**
     * Get the peer address this socket is connected to, or null if unconnected.
     */
    public function getPeerAddress(): null|Network\Address
    {
        return $this->peerAddress;
    }

    /**
     * Enable or disable broadcast on this socket.
     *
     * @throws Network\Exception\RuntimeException If the option cannot be set.
     */
    public function setBroadcast(bool $enabled): void
    {
        $socket = $this->importSocket();
        if (!@socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, $enabled ? 1 : 0)) {
            throw new Network\Exception\RuntimeException('Failed to set SO_BROADCAST option.');
        }
    }

    /**
     * Get the current broadcast setting.
     *
     * @throws Network\Exception\RuntimeException If the option cannot be read.
     */
    public function getBroadcast(): bool
    {
        $socket = $this->importSocket();
        $value = @socket_get_option($socket, SOL_SOCKET, SO_BROADCAST);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get SO_BROADCAST option.');
        }

        return (bool) $value;
    }

    /**
     * Set the IP Time-To-Live value for outgoing datagrams.
     *
     * @throws Network\Exception\RuntimeException If the option cannot be set.
     */
    public function setTtl(int $ttl): void
    {
        $socket = $this->importSocket();
        // IP_TTL: Linux = 2, macOS/BSD = 4 (not defined as a PHP constant)
        $option = PHP_OS_FAMILY === 'Darwin' ? 4 : 2;
        if (!@socket_set_option($socket, IPPROTO_IP, $option, $ttl)) {
            throw new Network\Exception\RuntimeException('Failed to set IP_TTL option.');
        }
    }

    /**
     * Get the current IP Time-To-Live value.
     *
     * @throws Network\Exception\RuntimeException If the option cannot be read.
     */
    public function getTtl(): int
    {
        $socket = $this->importSocket();
        // IP_TTL: Linux = 2, macOS/BSD = 4 (not defined as a PHP constant)
        $option = PHP_OS_FAMILY === 'Darwin' ? 4 : 2;
        $value = @socket_get_option($socket, IPPROTO_IP, $option);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get IP_TTL option.');
        }

        return (int) $value;
    }

    /**
     * Join an IPv4 multicast group.
     *
     * @param non-empty-string $multicast_address The multicast group address (e.g. '224.0.0.1').
     * @param non-empty-string $interface_address The local interface address (e.g. '0.0.0.0').
     *
     * @throws Network\Exception\RuntimeException If the operation fails.
     */
    public function joinMulticastV4(string $multicast_address, string $interface_address): void
    {
        $socket = $this->importSocket();
        $mreq = ['group' => $multicast_address, 'interface' => $interface_address];
        if (!@socket_set_option($socket, IPPROTO_IP, MCAST_JOIN_GROUP, $mreq)) {
            throw new Network\Exception\RuntimeException('Failed to join IPv4 multicast group.');
        }
    }

    /**
     * Leave an IPv4 multicast group.
     *
     * @param non-empty-string $multicast_address The multicast group address.
     * @param non-empty-string $interface_address The local interface address.
     *
     * @throws Network\Exception\RuntimeException If the operation fails.
     */
    public function leaveMulticastV4(string $multicast_address, string $interface_address): void
    {
        $socket = $this->importSocket();
        $mreq = ['group' => $multicast_address, 'interface' => $interface_address];
        if (!@socket_set_option($socket, IPPROTO_IP, MCAST_LEAVE_GROUP, $mreq)) {
            throw new Network\Exception\RuntimeException('Failed to leave IPv4 multicast group.');
        }
    }

    /**
     * Join an IPv6 multicast group.
     *
     * @param non-empty-string $multicast_address The multicast group address.
     * @param int<0, max> $interface_index The network interface index.
     *
     * @throws Network\Exception\RuntimeException If the operation fails.
     */
    public function joinMulticastV6(string $multicast_address, int $interface_index): void
    {
        $socket = $this->importSocket();
        $mreq = ['group' => $multicast_address, 'interface' => $interface_index];
        if (!@socket_set_option($socket, IPPROTO_IPV6, MCAST_JOIN_GROUP, $mreq)) {
            throw new Network\Exception\RuntimeException('Failed to join IPv6 multicast group.');
        }
    }

    /**
     * Leave an IPv6 multicast group.
     *
     * @param non-empty-string $multicast_address The multicast group address.
     * @param int<0, max> $interface_index The network interface index.
     *
     * @throws Network\Exception\RuntimeException If the operation fails.
     */
    public function leaveMulticastV6(string $multicast_address, int $interface_index): void
    {
        $socket = $this->importSocket();
        $mreq = ['group' => $multicast_address, 'interface' => $interface_index];
        if (!@socket_set_option($socket, IPPROTO_IPV6, MCAST_LEAVE_GROUP, $mreq)) {
            throw new Network\Exception\RuntimeException('Failed to leave IPv6 multicast group.');
        }
    }

    /**
     * @return resource|object|null
     */
    #[Override]
    public function getStream(): mixed
    {
        if (!is_resource($this->stream)) {
            return null;
        }

        return $this->stream;
    }

    #[Override]
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return resource
     */
    private function getResource(): mixed
    {
        if (!is_resource($this->stream)) {
            throw new IO\Exception\AlreadyClosedException('UDP socket has already been closed.');
        }

        return $this->stream;
    }

    /**
     * Import the underlying stream as a Socket resource.
     *
     * @throws Network\Exception\RuntimeException If the import fails.
     */
    private function importSocket(): \Socket
    {
        $stream = $this->getResource();
        $socket = @socket_import_stream($stream);
        if ($socket === false) {
            throw new Network\Exception\RuntimeException('Failed to import stream as socket.');
        }

        return $socket;
    }

    /**
     * Validate that the payload does not exceed the maximum UDP datagram size.
     *
     * @throws Network\Exception\InvalidArgumentException If the payload is too large.
     */
    private function validatePayloadSize(string $data): void
    {
        if (strlen($data) > self::MAX_DATAGRAM_SIZE) {
            throw new Network\Exception\InvalidArgumentException('UDP datagram payload exceeds maximum size of '
            . self::MAX_DATAGRAM_SIZE
            . ' bytes.');
        }
    }

    /**
     * Wait for the stream to become readable.
     *
     * @param resource $stream
     */
    private function awaitReadable(mixed $stream, null|Duration $timeout): void
    {
        $suspension = EventLoop::getSuspension();
        $timeout_watcher = null;

        if ($timeout !== null) {
            $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use (
                $suspension,
            ): void {
                $suspension->resume(true);
            });
        }

        $read_watcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(false);
        });

        /** @var bool $timed_out */
        $timed_out = $suspension->suspend();
        if ($timeout_watcher !== null) {
            EventLoop::cancel($timeout_watcher);
        }

        EventLoop::cancel($read_watcher);

        if ($timed_out) {
            throw new IO\Exception\TimeoutException('UDP receive operation timed out.');
        }
    }

    /**
     * Wait for the stream to be writable with a timeout.
     *
     * @param resource $stream
     */
    private function waitWritable(mixed $stream, Duration $timeout): void
    {
        $suspension = EventLoop::getSuspension();
        $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use ($suspension): void {
            $suspension->resume(true);
        });

        $write_watcher = EventLoop::onWritable($stream, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(false);
        });

        /** @var bool $timed_out */
        $timed_out = $suspension->suspend();
        EventLoop::cancel($timeout_watcher);
        EventLoop::cancel($write_watcher);

        if ($timed_out) {
            throw new IO\Exception\TimeoutException('UDP send operation timed out.');
        }
    }

    /**
     * Parse a "host:port" or "[host]:port" string into an Address.
     *
     * Handles both IPv4 ("127.0.0.1:8080") and IPv6 ("[::1]:8080") formats.
     *
     * @throws Network\Exception\RuntimeException If the address is invalid.
     */
    private function parseAddress(string $address): Network\Address
    {
        if ($address === '') {
            return Network\Address::udp('0.0.0.0', 0);
        }

        // IPv6 bracket notation: [host]:port
        if (str_starts_with($address, '[')) {
            $close_bracket = strpos($address, ']');
            if ($close_bracket === false) {
                return Network\Address::udp($address, 0);
            }

            $host = substr($address, 1, $close_bracket - 1);
            if ($host === '') {
                $host = '::';
            }

            // Check for :port after the closing bracket
            $port = 0;
            if (($close_bracket + 1) < strlen($address) && $address[$close_bracket + 1] === ':') {
                $port = (int) substr($address, $close_bracket + 2);
            }

            if ($port < 0 || $port > 65_535) {
                throw new Network\Exception\RuntimeException("Invalid port number in address: {$port}");
            }

            return Network\Address::udp($host, $port);
        }

        // IPv4: host:port
        $last_colon = strrpos($address, ':');
        if ($last_colon === false) {
            return Network\Address::udp($address, 0);
        }

        $host = substr($address, 0, $last_colon);
        $port = (int) substr($address, $last_colon + 1);

        $host = $host !== '' ? $host : '0.0.0.0';
        if ($port < 0 || $port > 65_535) {
            throw new Network\Exception\RuntimeException("Invalid port number in address: {$port}");
        }

        return Network\Address::udp($host, $port);
    }
}
