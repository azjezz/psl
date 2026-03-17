<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Aead;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Aead;
use Psl\Crypto\Exception;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;

use function sodium_crypto_aead_aes256gcm_is_available;

final class FunctionsTest extends TestCase
{
    public function testXChaCha20Poly1305Roundtrip(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);
        $ad = 'additional-data';

        $ciphertext = Aead\encrypt('hello', $key, $nonce, $ad, Aead\Algorithm::XChaCha20Poly1305);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, $ad, Aead\Algorithm::XChaCha20Poly1305);

        static::assertSame('hello', $plaintext);
    }

    public function testChaCha20Poly1305Roundtrip(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::ChaCha20Poly1305);
        $nonce = SecureRandom\bytes(12);

        $ciphertext = Aead\encrypt('test', $key, $nonce, '', Aead\Algorithm::ChaCha20Poly1305);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, '', Aead\Algorithm::ChaCha20Poly1305);

        static::assertSame('test', $plaintext);
    }

    public function testAes256GcmRoundtrip(): void
    {
        if (!sodium_crypto_aead_aes256gcm_is_available()) {
            static::markTestSkipped('AES-256-GCM is not available on this platform.');
        }

        $key = Aead\generate_key(Aead\Algorithm::Aes256Gcm);
        $nonce = SecureRandom\bytes(12);

        $ciphertext = Aead\encrypt('aes test', $key, $nonce, 'aes-gcm-ad', Aead\Algorithm::Aes256Gcm);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, 'aes-gcm-ad', Aead\Algorithm::Aes256Gcm);

        static::assertSame('aes test', $plaintext);
    }

    public function testDecryptionFailsWithWrongKey(): void
    {
        $key1 = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $key2 = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $ciphertext = Aead\encrypt('hello', $key1, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('AEAD decryption failed.');
        Aead\decrypt($ciphertext, $key2, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);
    }

    public function testDecryptionFailsWithWrongNonce(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce1 = SecureRandom\bytes(24);
        $nonce2 = SecureRandom\bytes(24);

        $ciphertext = Aead\encrypt('hello', $key, $nonce1, '', Aead\Algorithm::XChaCha20Poly1305);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('AEAD decryption failed.');
        Aead\decrypt($ciphertext, $key, $nonce2, '', Aead\Algorithm::XChaCha20Poly1305);
    }

    public function testDecryptionFailsWithWrongAdditionalData(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $ciphertext = Aead\encrypt('hello', $key, $nonce, 'correct-ad', Aead\Algorithm::XChaCha20Poly1305);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('AEAD decryption failed.');
        Aead\decrypt($ciphertext, $key, $nonce, 'wrong-ad', Aead\Algorithm::XChaCha20Poly1305);
    }

    public function testDecryptionFailsWithTamperedCiphertext(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $ciphertext = Aead\encrypt('hello', $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);
        $tampered = $ciphertext;
        $tampered[0] = Byte\chr(Byte\ord($tampered[0]) ^ 0x01);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('AEAD decryption failed.');
        Aead\decrypt($tampered, $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);
    }

    public function testEmptyPlaintextRoundtrip(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $ciphertext = Aead\encrypt('', $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);

        static::assertSame('', $plaintext);
    }

    public function testGenerateKeyProducesValidLength(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        static::assertSame(Aead\KEY_BYTES, Byte\length($key->bytes));

        $key = Aead\generate_key(Aead\Algorithm::ChaCha20Poly1305);
        static::assertSame(Aead\KEY_BYTES, Byte\length($key->bytes));
    }

    public function testGenerateKeyProducesUniqueKeys(): void
    {
        $key1 = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $key2 = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);

        static::assertNotSame($key1->bytes, $key2->bytes);
    }

    public function testCiphertextIsLongerThanPlaintext(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $plaintext = 'hello';
        $ciphertext = Aead\encrypt($plaintext, $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);

        static::assertGreaterThan(Byte\length($plaintext), Byte\length($ciphertext));
    }

    public function testSameInputProducesSameCiphertext(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $ct1 = Aead\encrypt('hello', $key, $nonce, 'ad', Aead\Algorithm::XChaCha20Poly1305);
        $ct2 = Aead\encrypt('hello', $key, $nonce, 'ad', Aead\Algorithm::XChaCha20Poly1305);

        static::assertSame($ct1, $ct2);
    }

    public function testChaCha20Poly1305DecryptionFailsWithWrongKey(): void
    {
        $key1 = Aead\generate_key(Aead\Algorithm::ChaCha20Poly1305);
        $key2 = Aead\generate_key(Aead\Algorithm::ChaCha20Poly1305);
        $nonce = SecureRandom\bytes(12);

        $ciphertext = Aead\encrypt('test', $key1, $nonce, '', Aead\Algorithm::ChaCha20Poly1305);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('AEAD decryption failed.');
        Aead\decrypt($ciphertext, $key2, $nonce, '', Aead\Algorithm::ChaCha20Poly1305);
    }

    public function testChaCha20Poly1305EmptyPlaintext(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::ChaCha20Poly1305);
        $nonce = SecureRandom\bytes(12);

        $ciphertext = Aead\encrypt('', $key, $nonce, 'ad', Aead\Algorithm::ChaCha20Poly1305);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, 'ad', Aead\Algorithm::ChaCha20Poly1305);

        static::assertSame('', $plaintext);
    }

    public function testLargeAdditionalData(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);
        $ad = Str\repeat('A', 10_000);

        $ciphertext = Aead\encrypt('data', $key, $nonce, $ad, Aead\Algorithm::XChaCha20Poly1305);
        $plaintext = Aead\decrypt($ciphertext, $key, $nonce, $ad, Aead\Algorithm::XChaCha20Poly1305);

        static::assertSame('data', $plaintext);
    }

    public function testKeyWrongLengthExceptionMessage(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('AEAD key must be exactly 32 bytes.');

        new Aead\Key('too-short');
    }

    public function testLargePlaintext(): void
    {
        $key = Aead\generate_key(Aead\Algorithm::XChaCha20Poly1305);
        $nonce = SecureRandom\bytes(24);

        $plaintext = Str\repeat('B', 100_000);
        $ciphertext = Aead\encrypt($plaintext, $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);
        $decrypted = Aead\decrypt($ciphertext, $key, $nonce, '', Aead\Algorithm::XChaCha20Poly1305);

        static::assertSame($plaintext, $decrypted);
    }
}
