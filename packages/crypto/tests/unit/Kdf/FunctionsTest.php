<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Kdf;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Kdf;
use Psl\Str\Byte;

use function array_unique;
use function count;

final class FunctionsTest extends TestCase
{
    public function testDeriveProducesDeterministicOutput(): void
    {
        $key = Kdf\generate_key();

        $subkey1 = Kdf\derive($key, 1, 'mycontxt');
        $subkey2 = Kdf\derive($key, 1, 'mycontxt');

        static::assertSame($subkey1, $subkey2);
    }

    public function testDifferentSubkeyIdProducesDifferentOutput(): void
    {
        $key = Kdf\generate_key();

        $subkey1 = Kdf\derive($key, 1, 'mycontxt');
        $subkey2 = Kdf\derive($key, 2, 'mycontxt');

        static::assertNotSame($subkey1, $subkey2);
    }

    public function testDifferentContextProducesDifferentOutput(): void
    {
        $key = Kdf\generate_key();

        $subkey1 = Kdf\derive($key, 1, 'context1');
        $subkey2 = Kdf\derive($key, 1, 'context2');

        static::assertNotSame($subkey1, $subkey2);
    }

    public function testInvalidContextLengthThrows(): void
    {
        $key = Kdf\generate_key();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('KDF context must be exactly 8 bytes.');
        Kdf\derive($key, 1, 'too-long-context');
    }

    public function testContextTooShortThrows(): void
    {
        $key = Kdf\generate_key();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('KDF context must be exactly 8 bytes.');
        Kdf\derive($key, 1, 'short');
    }

    public function testCustomLength(): void
    {
        $key = Kdf\generate_key();

        $subkey = Kdf\derive($key, 1, 'mycontxt', 16);
        static::assertSame(16, Byte\length($subkey));
    }

    public function testGenerateKeyProducesValidLength(): void
    {
        $key = Kdf\generate_key();
        static::assertSame(Kdf\KEY_BYTES, Byte\length($key->bytes));
    }

    public function testGenerateKeyProducesUniqueKeys(): void
    {
        $key1 = Kdf\generate_key();
        $key2 = Kdf\generate_key();

        static::assertNotSame($key1->bytes, $key2->bytes);
    }

    public function testDifferentMasterKeysProduceDifferentOutput(): void
    {
        $key1 = Kdf\generate_key();
        $key2 = Kdf\generate_key();

        $subkey1 = Kdf\derive($key1, 1, 'mycontxt');
        $subkey2 = Kdf\derive($key2, 1, 'mycontxt');

        static::assertNotSame($subkey1, $subkey2);
    }

    public function testMinDerivedLength(): void
    {
        $key = Kdf\generate_key();

        $subkey = Kdf\derive($key, 1, 'mycontxt', Kdf\DERIVED_MIN_BYTES);
        static::assertSame(Kdf\DERIVED_MIN_BYTES, Byte\length($subkey));
    }

    public function testMaxDerivedLength(): void
    {
        $key = Kdf\generate_key();

        $subkey = Kdf\derive($key, 1, 'mycontxt', Kdf\DERIVED_MAX_BYTES);
        static::assertSame(Kdf\DERIVED_MAX_BYTES, Byte\length($subkey));
    }

    public function testDefaultDerivedLength(): void
    {
        $key = Kdf\generate_key();

        $subkey = Kdf\derive($key, 1, 'mycontxt');
        static::assertSame(32, Byte\length($subkey));
    }

    public function testSubkeyIdZero(): void
    {
        $key = Kdf\generate_key();

        $subkey = Kdf\derive($key, 0, 'mycontxt');
        static::assertSame(32, Byte\length($subkey));
    }

    public function testManySubkeysAreUnique(): void
    {
        $key = Kdf\generate_key();
        $subkeys = [];

        for ($i = 0; $i < 10; $i++) {
            $subkeys[] = Kdf\derive($key, $i, 'mycontxt');
        }

        static::assertSame(count($subkeys), count(array_unique($subkeys)));
    }

    public function testVariousLengths(): void
    {
        $key = Kdf\generate_key();

        foreach ([16, 20, 24, 32, 48, 64] as $length) {
            $subkey = Kdf\derive($key, 1, 'mycontxt', $length);
            static::assertSame($length, Byte\length($subkey), "Failed for length {$length}");
        }
    }
}
