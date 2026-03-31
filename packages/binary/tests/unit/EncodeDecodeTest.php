<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Binary;
use Psl\Binary\Endianness;
use Psl\Binary\Exception;
use Psl\Math;

use function is_nan;

use const INF;
use const NAN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class EncodeDecodeTest extends TestCase
{
    // --- u8 ---

    #[DataProvider('provideU8Values')]
    public function testU8RoundTrip(int $value): void
    {
        static::assertSame($value, Binary\decode_u8(Binary\encode_u8($value)));
    }

    public static function provideU8Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'max' => [255];
        yield 'mid' => [128];
    }

    public function testU8KnownBytes(): void
    {
        static::assertSame("\x00", Binary\encode_u8(0));
        static::assertSame("\xFF", Binary\encode_u8(255));
        static::assertSame("\x2A", Binary\encode_u8(42));
    }

    public function testEncodeU8OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u8(256);
    }

    public function testEncodeU8OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u8(-1);
    }

    public function testDecodeU8Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_u8('');
    }

    // --- u16 ---

    #[DataProvider('provideU16Values')]
    public function testU16RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_u16(Binary\encode_u16($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideU16Values')]
    public function testU16RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_u16(
            Binary\encode_u16($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideU16Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'max' => [65_535];
        yield '0x0102' => [0x0102];
    }

    public function testU16KnownBytes(): void
    {
        static::assertSame("\x01\x02", Binary\encode_u16(0x0102, Endianness::Big));
        static::assertSame("\x02\x01", Binary\encode_u16(0x0102, Endianness::Little));
    }

    public function testU16DefaultEndianness(): void
    {
        // Default is Big
        static::assertSame("\x01\x02", Binary\encode_u16(0x0102));
        static::assertSame(0x0102, Binary\decode_u16("\x01\x02"));
    }

    public function testEncodeU16OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u16(65_536);
    }

    public function testEncodeU16OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u16(-1);
    }

    public function testDecodeU16Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_u16("\x00");
    }

    // --- u32 ---

    #[DataProvider('provideU32Values')]
    public function testU32RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_u32(Binary\encode_u32($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideU32Values')]
    public function testU32RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_u32(
            Binary\encode_u32($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideU32Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'max' => [4_294_967_295];
        yield '0x01020304' => [0x0102_0304];
    }

    public function testU32KnownBytes(): void
    {
        static::assertSame("\x01\x02\x03\x04", Binary\encode_u32(0x0102_0304, Endianness::Big));
        static::assertSame("\x04\x03\x02\x01", Binary\encode_u32(0x0102_0304, Endianness::Little));
    }

    public function testEncodeU32OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u32(4_294_967_296);
    }

    public function testEncodeU32OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u32(-1);
    }

    public function testDecodeU32Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_u32("\x00\x00\x00");
    }

    // --- u64 ---

    #[DataProvider('provideU64Values')]
    public function testU64RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_u64(Binary\encode_u64($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideU64Values')]
    public function testU64RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_u64(
            Binary\encode_u64($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideU64Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'max' => [PHP_INT_MAX];
        yield 'large' => [0x0102_0304_0506_0708];
    }

    public function testU64KnownBytes(): void
    {
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", Binary\encode_u64(
            0x0102_0304_0506_0708,
            Endianness::Big,
        ));
        static::assertSame("\x08\x07\x06\x05\x04\x03\x02\x01", Binary\encode_u64(
            0x0102_0304_0506_0708,
            Endianness::Little,
        ));
    }

    public function testEncodeU64OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_u64(-1);
    }

    public function testDecodeU64Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_u64("\x00\x00\x00\x00\x00\x00\x00");
    }

    public function testDecodeU64OverflowBig(): void
    {
        $this->expectException(Exception\OverflowException::class);

        // 0xFF repeated 8 times = max uint64 which exceeds PHP_INT_MAX
        Binary\decode_u64("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", Endianness::Big);
    }

    public function testDecodeU64OverflowLittle(): void
    {
        $this->expectException(Exception\OverflowException::class);

        Binary\decode_u64("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", Endianness::Little);
    }

    // --- i8 ---

    #[DataProvider('provideI8Values')]
    public function testI8RoundTrip(int $value): void
    {
        static::assertSame($value, Binary\decode_i8(Binary\encode_i8($value)));
    }

    public static function provideI8Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'neg_one' => [-1];
        yield 'max' => [127];
        yield 'min' => [-128];
    }

    public function testEncodeI8OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i8(128);
    }

    public function testEncodeI8OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i8(-129);
    }

    public function testDecodeI8Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_i8('');
    }

    // --- i16 ---

    #[DataProvider('provideI16Values')]
    public function testI16RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_i16(Binary\encode_i16($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideI16Values')]
    public function testI16RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_i16(
            Binary\encode_i16($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideI16Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'neg_one' => [-1];
        yield 'max' => [32_767];
        yield 'min' => [-32_768];
    }

    public function testI16KnownBytes(): void
    {
        // -1 as i16 big endian = 0xFFFF
        static::assertSame("\xFF\xFF", Binary\encode_i16(-1, Endianness::Big));
        static::assertSame("\xFF\xFF", Binary\encode_i16(-1, Endianness::Little));
    }

    public function testEncodeI16OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i16(32_768);
    }

    public function testEncodeI16OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i16(-32_769);
    }

    public function testDecodeI16Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_i16("\x00");
    }

    // --- i32 ---

    #[DataProvider('provideI32Values')]
    public function testI32RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_i32(Binary\encode_i32($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideI32Values')]
    public function testI32RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_i32(
            Binary\encode_i32($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideI32Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'neg_one' => [-1];
        yield 'max' => [2_147_483_647];
        yield 'min' => [-2_147_483_648];
    }

    public function testI32KnownBytes(): void
    {
        // -1 as i32 big endian = 0xFFFFFFFF
        static::assertSame("\xFF\xFF\xFF\xFF", Binary\encode_i32(-1, Endianness::Big));
        static::assertSame("\xFF\xFF\xFF\xFF", Binary\encode_i32(-1, Endianness::Little));
    }

    public function testEncodeI32OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i32(2_147_483_648);
    }

    public function testEncodeI32OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_i32(-2_147_483_649);
    }

    public function testDecodeI32Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_i32("\x00\x00\x00");
    }

    // --- i64 ---

    #[DataProvider('provideI64Values')]
    public function testI64RoundTripBig(int $value): void
    {
        static::assertSame($value, Binary\decode_i64(Binary\encode_i64($value, Endianness::Big), Endianness::Big));
    }

    #[DataProvider('provideI64Values')]
    public function testI64RoundTripLittle(int $value): void
    {
        static::assertSame($value, Binary\decode_i64(
            Binary\encode_i64($value, Endianness::Little),
            Endianness::Little,
        ));
    }

    public static function provideI64Values(): iterable
    {
        yield 'zero' => [0];
        yield 'one' => [1];
        yield 'neg_one' => [-1];
        yield 'max' => [PHP_INT_MAX];
        yield 'min' => [PHP_INT_MIN];
    }

    public function testI64KnownBytes(): void
    {
        static::assertSame("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", Binary\encode_i64(-1, Endianness::Big));
        static::assertSame("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", Binary\encode_i64(-1, Endianness::Little));
    }

    public function testDecodeI64Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_i64("\x00\x00\x00\x00\x00\x00\x00");
    }

    // --- f32 ---

    #[DataProvider('provideF32Values')]
    public function testF32RoundTripBig(float $value): void
    {
        $decoded = Binary\decode_f32(Binary\encode_f32($value, Endianness::Big), Endianness::Big);
        if (is_nan($value)) {
            static::assertNan($decoded);
        } else {
            static::assertSame($value, $decoded);
        }
    }

    #[DataProvider('provideF32Values')]
    public function testF32RoundTripLittle(float $value): void
    {
        $decoded = Binary\decode_f32(Binary\encode_f32($value, Endianness::Little), Endianness::Little);
        if (is_nan($value)) {
            static::assertNan($decoded);
        } else {
            static::assertSame($value, $decoded);
        }
    }

    public static function provideF32Values(): iterable
    {
        yield 'zero' => [0.0];
        yield 'one' => [1.0];
        yield 'neg_one' => [-1.0];
        yield 'half' => [0.5];
        yield 'inf' => [INF];
        yield 'neg_inf' => [-INF];
        yield 'nan' => [NAN];
    }

    public function testEncodeF32OverflowHigh(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_f32(Math\FLOAT32_MAX * 2.0);
    }

    public function testEncodeF32OverflowLow(): void
    {
        $this->expectException(Exception\OverflowException::class);
        Binary\encode_f32(Math\FLOAT32_MIN * 2.0);
    }

    public function testDecodeF32Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_f32("\x00\x00\x00");
    }

    // --- f64 ---

    #[DataProvider('provideF64Values')]
    public function testF64RoundTripBig(float $value): void
    {
        $decoded = Binary\decode_f64(Binary\encode_f64($value, Endianness::Big), Endianness::Big);
        if (is_nan($value)) {
            static::assertNan($decoded);
        } else {
            static::assertSame($value, $decoded);
        }
    }

    #[DataProvider('provideF64Values')]
    public function testF64RoundTripLittle(float $value): void
    {
        $decoded = Binary\decode_f64(Binary\encode_f64($value, Endianness::Little), Endianness::Little);
        if (is_nan($value)) {
            static::assertNan($decoded);
        } else {
            static::assertSame($value, $decoded);
        }
    }

    public static function provideF64Values(): iterable
    {
        yield 'zero' => [0.0];
        yield 'one' => [1.0];
        yield 'neg_one' => [-1.0];
        yield 'pi' => [3.141_592_653_589_793];
        yield 'inf' => [INF];
        yield 'neg_inf' => [-INF];
        yield 'nan' => [NAN];
        yield 'very_large' => [1.797_693_134_862_315_7e+308];
        yield 'very_small' => [5e-324];
    }

    public function testDecodeF64Underflow(): void
    {
        $this->expectException(Exception\UnderflowException::class);
        Binary\decode_f64("\x00\x00\x00\x00\x00\x00\x00");
    }

    // --- Cross-endianness tests ---

    public function testEndiannessMismatchU16(): void
    {
        $encoded = Binary\encode_u16(0x0102, Endianness::Big);
        $decoded = Binary\decode_u16($encoded, Endianness::Little);
        // Big-endian 0x0102 → bytes 01 02 → read as little-endian → 0x0201
        static::assertSame(0x0201, $decoded);
    }

    public function testEndiannessMismatchU32(): void
    {
        $encoded = Binary\encode_u32(0x0102_0304, Endianness::Big);
        $decoded = Binary\decode_u32($encoded, Endianness::Little);
        static::assertSame(0x0403_0201, $decoded);
    }
}
