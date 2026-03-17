<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Crypto;
use Psl\Str;
use Psl\Str\Byte;

final class InvalidArgumentExceptionTest extends TestCase
{
    public function testEncryptionKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Symmetric\Key('too-short');
    }

    public function testEncryptionKeyValidLength(): void
    {
        $key = new Crypto\Symmetric\Key(Str\repeat('a', Crypto\Symmetric\KEY_BYTES));
        static::assertSame(Crypto\Symmetric\KEY_BYTES, Byte\length($key->bytes));
    }

    public function testAeadKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Aead\Key('too-short');
    }

    public function testAeadKeyValidLength(): void
    {
        $key = new Crypto\Aead\Key(Str\repeat('a', Crypto\Aead\KEY_BYTES));
        static::assertSame(Crypto\Aead\KEY_BYTES, Byte\length($key->bytes));
    }

    public function testStreamCipherKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\StreamCipher\Key('too-short');
    }

    public function testStreamCipherKeyAccepts16Bytes(): void
    {
        $key = new Crypto\StreamCipher\Key(Str\repeat('a', 16));
        static::assertSame(16, Byte\length($key->bytes));
    }

    public function testStreamCipherKeyAccepts32Bytes(): void
    {
        $key = new Crypto\StreamCipher\Key(Str\repeat('a', 32));
        static::assertSame(32, Byte\length($key->bytes));
    }

    public function testSigningSecretKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Signing\SecretKey('too-short');
    }

    public function testSigningPublicKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Signing\PublicKey('too-short');
    }

    public function testSignatureWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Signing\Signature('too-short');
    }

    public function testAsymmetricEncryptionSecretKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Asymmetric\SecretKey('too-short');
    }

    public function testAsymmetricEncryptionPublicKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Asymmetric\PublicKey('too-short');
    }

    public function testKeyExchangeSecretKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\KeyExchange\SecretKey('too-short');
    }

    public function testKeyExchangePublicKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\KeyExchange\PublicKey('too-short');
    }

    public function testKeyExchangeSharedSecretWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\KeyExchange\SharedSecret('too-short');
    }

    public function testKdfKeyWrongLength(): void
    {
        $this->expectException(Crypto\Exception\InvalidArgumentException::class);
        new Crypto\Kdf\Key('too-short');
    }

    public function testKdfKeyValidLength(): void
    {
        $key = new Crypto\Kdf\Key(Str\repeat('a', Crypto\Kdf\KEY_BYTES));
        static::assertSame(Crypto\Kdf\KEY_BYTES, Byte\length($key->bytes));
    }
}
