<?php

declare(strict_types=1);

namespace Psl\Binary;

/**
 * Interface for binary data readers.
 *
 * Each read method consumes data and advances the reader. All methods throw
 * {@see Exception\UnderflowException} if insufficient data remains.
 */
interface ReaderInterface
{
    /**
     * Read an unsigned 8-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<0, 255>
     */
    public function u8(): int;

    /**
     * Read an unsigned 16-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<0, 65535>
     */
    public function u16(null|Endianness $endianness = null): int;

    /**
     * Read an unsigned 32-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<0, 4294967295>
     */
    public function u32(null|Endianness $endianness = null): int;

    /**
     * Read an unsigned 64-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     * @throws Exception\OverflowException If the decoded value exceeds PHP_INT_MAX.
     *
     * @return int<0, max>
     */
    public function u64(null|Endianness $endianness = null): int;

    /**
     * Read a signed 8-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<-128, 127>
     */
    public function i8(): int;

    /**
     * Read a signed 16-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<-32768, 32767>
     */
    public function i16(null|Endianness $endianness = null): int;

    /**
     * Read a signed 32-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     *
     * @return int<-2147483648, 2147483647>
     */
    public function i32(null|Endianness $endianness = null): int;

    /**
     * Read a signed 64-bit integer.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function i64(null|Endianness $endianness = null): int;

    /**
     * Read a 32-bit floating point value.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function f32(null|Endianness $endianness = null): float;

    /**
     * Read a 64-bit floating point value.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function f64(null|Endianness $endianness = null): float;

    /**
     * Read the specified number of raw bytes.
     *
     * @param int<0, max> $length
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function bytes(int $length): string;

    /**
     * Skip the specified number of bytes without returning them.
     *
     * For handle-based readers, this will use seeking when the underlying handle
     * supports {@see \Psl\IO\SeekHandleInterface}, otherwise it reads and discards
     * the bytes.
     *
     * @param int<0, max> $length
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function skip(int $length): void;

    /**
     * Read a length-prefixed byte string with a u8 length prefix.
     *
     * Reads a u8 as the length, then reads that many bytes.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function u8PrefixedBytes(): string;

    /**
     * Read a length-prefixed byte string with a u16 length prefix.
     *
     * Reads a u16 as the length, then reads that many bytes.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function u16PrefixedBytes(null|Endianness $endianness = null): string;

    /**
     * Read a length-prefixed byte string with a u32 length prefix.
     *
     * Reads a u32 as the length, then reads that many bytes.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    public function u32PrefixedBytes(null|Endianness $endianness = null): string;

    /**
     * Read a length-prefixed byte string with a u64 length prefix.
     *
     * Reads a u64 as the length, then reads that many bytes.
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     * @throws Exception\OverflowException If the decoded length exceeds PHP_INT_MAX.
     */
    public function u64PrefixedBytes(null|Endianness $endianness = null): string;

    /**
     * Return whether all data has been consumed.
     */
    public function isConsumed(): bool;
}
