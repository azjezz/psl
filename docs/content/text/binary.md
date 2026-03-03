# Binary

The `Binary` component provides typed functions and classes for encoding and decoding binary data, replacing PHP's cryptic `pack()` / `unpack()` format strings with a clear, type-safe API. It supports explicit endianness control via the `Endianness` enum.

## Why Use This?

PHP's `pack()` and `unpack()` rely on single-character format codes (`N`, `v`, `J`, `P`, etc.) that are hard to remember and easy to misuse. PSL's Binary component provides:

- **Named functions** like `encode_u32()` and `decode_i16()` instead of cryptic format strings.
- **Explicit endianness** via an enum instead of remembering which format code is big-endian vs little-endian.
- **Type-safe validation** that throws on overflow or underflow.
- **An immutable Writer** for building binary messages in memory.
- **A cursor-based Reader** for parsing binary data from a string.
- **Handle-based Writer and Reader** for streaming binary data directly to/from IO handles without buffering.

## One-Shot Functions

For encoding or decoding individual values, use the standalone functions. Each function validates its input and throws on error.

@example('other/binary-oneshot.php')

## Writer

The `Writer` is an immutable builder for binary data. Each method returns a new instance with the appended bytes. Use this when you need the result as a string.

@example('other/binary-writer.php')

## Reader

The `Reader` provides cursor-based sequential reading of binary data from a string. Each read advances the internal cursor.

@example('other/binary-reader.php')

## Handle-Based IO

`HandleWriter` and `HandleReader` operate directly on `IO\WriteHandleInterface` and `IO\ReadHandleInterface`, writing and reading binary data without buffering entire messages in PHP memory. This is ideal for network protocols over TCP/Unix sockets.

@example('other/binary-handle.php')

## Length-Prefixed Bytes

All writers and readers support length-prefixed byte strings, a common pattern in binary protocols where the payload length is written before the payload itself. The prefix size determines the maximum payload length.

@example('other/binary-prefixed.php')

## Implementing Custom Writers and Readers

The `WriterConvenienceMethodsTrait` and `ReaderConvenienceMethodsTrait` provide default implementations of `skip()` and the length-prefixed methods. When implementing `WriterInterface` or `ReaderInterface`, use these traits to avoid re-implementing the convenience methods:

```php
use Psl\Binary\WriterInterface;
use Psl\Binary\WriterConvenienceMethodsTrait;

final class MyCustomWriter implements WriterInterface
{
    use WriterConvenienceMethodsTrait;

    // Only implement the primitive methods: u8, u16, u32, u64,
    // i8, i16, i32, i64, f32, f64, bytes
    // The prefixed methods come from the trait for free.
}
```

## Supported Types

| Type | Size | Signed | Function Pair |
|------|------|--------|---------------|
| u8 | 1 byte | No | `encode_u8` / `decode_u8` |
| u16 | 2 bytes | No | `encode_u16` / `decode_u16` |
| u32 | 4 bytes | No | `encode_u32` / `decode_u32` |
| u64 | 8 bytes | No | `encode_u64` / `decode_u64` |
| i8 | 1 byte | Yes | `encode_i8` / `decode_i8` |
| i16 | 2 bytes | Yes | `encode_i16` / `decode_i16` |
| i32 | 4 bytes | Yes | `encode_i32` / `decode_i32` |
| i64 | 8 bytes | Yes | `encode_i64` / `decode_i64` |
| f32 | 4 bytes | N/A | `encode_f32` / `decode_f32` |
| f64 | 8 bytes | N/A | `encode_f64` / `decode_f64` |

See `src/Psl/Binary/` for the full API.
