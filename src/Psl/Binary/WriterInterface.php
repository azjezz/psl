<?php

declare(strict_types=1);

namespace Psl\Binary;

/**
 * Interface for binary data writers.
 *
 * Each write method returns the writer instance for chaining.
 */
interface WriterInterface
{
    /**
     * Write an unsigned 8-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function u8(int $value): static;

    /**
     * Write an unsigned 16-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function u16(int $value, null|Endianness $endianness = null): static;

    /**
     * Write an unsigned 32-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function u32(int $value, null|Endianness $endianness = null): static;

    /**
     * Write an unsigned 64-bit integer.
     *
     * @throws Exception\OverflowException If $value is negative.
     */
    public function u64(int $value, null|Endianness $endianness = null): static;

    /**
     * Write a signed 8-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function i8(int $value): static;

    /**
     * Write a signed 16-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function i16(int $value, null|Endianness $endianness = null): static;

    /**
     * Write a signed 32-bit integer.
     *
     * @throws Exception\OverflowException If $value is out of range.
     */
    public function i32(int $value, null|Endianness $endianness = null): static;

    /**
     * Write a signed 64-bit integer.
     */
    public function i64(int $value, null|Endianness $endianness = null): static;

    /**
     * Write a 32-bit floating point value.
     *
     * @throws Exception\OverflowException If the finite $value exceeds float32 range.
     */
    public function f32(float $value, null|Endianness $endianness = null): static;

    /**
     * Write a 64-bit floating point value.
     */
    public function f64(float $value, null|Endianness $endianness = null): static;

    /**
     * Write raw bytes.
     */
    public function bytes(string $value): static;

    /**
     * Write a length-prefixed byte string with a u8 length prefix.
     *
     * Writes the string length as a u8 followed by the raw bytes.
     *
     * @throws Exception\OverflowException If the string length exceeds 255 bytes.
     */
    public function u8PrefixedBytes(string $value): static;

    /**
     * Write a length-prefixed byte string with a u16 length prefix.
     *
     * Writes the string length as a u16 followed by the raw bytes.
     *
     * @throws Exception\OverflowException If the string length exceeds 65535 bytes.
     */
    public function u16PrefixedBytes(string $value, null|Endianness $endianness = null): static;

    /**
     * Write a length-prefixed byte string with a u32 length prefix.
     *
     * Writes the string length as a u32 followed by the raw bytes.
     *
     * @throws Exception\OverflowException If the string length exceeds 4294967295 bytes.
     */
    public function u32PrefixedBytes(string $value, null|Endianness $endianness = null): static;

    /**
     * Write a length-prefixed byte string with a u64 length prefix.
     *
     * Writes the string length as a u64 followed by the raw bytes.
     */
    public function u64PrefixedBytes(string $value, null|Endianness $endianness = null): static;
}
