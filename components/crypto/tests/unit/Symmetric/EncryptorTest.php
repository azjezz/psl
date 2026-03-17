<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Symmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Symmetric;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;

final class EncryptorTest extends TestCase
{
    public function testSealAndOpenRoundtrip(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $plaintext = 'Hello, World!';
        $ciphertext = $encryptor->seal($plaintext);

        static::assertNotSame($plaintext, $ciphertext);
        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testSealAndOpenWithAdditionalData(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $plaintext = 'Secret message';
        $ciphertext = $encryptor->seal($plaintext, 'context-info');

        static::assertSame($plaintext, $encryptor->open($ciphertext, 'context-info'));
    }

    public function testOpenWithWrongAdditionalDataFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $ciphertext = $encryptor->seal('test', 'correct-ad');

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');
        $encryptor->open($ciphertext, 'wrong-ad');
    }

    public function testOpenWithMissingAdditionalDataFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $ciphertext = $encryptor->seal('test', 'some-ad');

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');
        $encryptor->open($ciphertext);
    }

    public function testOpenWithWrongKeyFails(): void
    {
        $key1 = Symmetric\generate_key();
        $key2 = Symmetric\generate_key();

        $ciphertext = new Symmetric\Encryptor($key1)->seal('test');

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');
        new Symmetric\Encryptor($key2)->open($ciphertext);
    }

    public function testOpenWithBadCiphertextFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Ciphertext is too short.');
        $encryptor->open('too-short');
    }

    public function testOpenWithTamperedCiphertextFails(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $ciphertext = $encryptor->seal('hello');
        $tampered = $ciphertext;
        $last = Byte\length($tampered) - 1;
        $tampered[$last] = Byte\chr(Byte\ord($tampered[$last]) ^ 0x01);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');
        $encryptor->open($tampered);
    }

    public function testFunctionWrappers(): void
    {
        $key = Symmetric\generate_key();

        $ciphertext = Symmetric\seal('Function wrapper test', $key);

        static::assertSame('Function wrapper test', Symmetric\open($ciphertext, $key));
    }

    public function testFunctionWrappersWithAdditionalData(): void
    {
        $key = Symmetric\generate_key();

        $ciphertext = Symmetric\seal('data', $key, 'ad-context');
        static::assertSame('data', Symmetric\open($ciphertext, $key, 'ad-context'));
    }

    public function testFunctionWrapperOpenWithWrongAdFails(): void
    {
        $key = Symmetric\generate_key();

        $ciphertext = Symmetric\seal('data', $key, 'correct');

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');
        Symmetric\open($ciphertext, $key, 'wrong');
    }

    public function testEmptyPlaintext(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $ciphertext = $encryptor->seal('');
        static::assertSame('', $encryptor->open($ciphertext));
    }

    public function testSealProducesDifferentCiphertextEachTime(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $ct1 = $encryptor->seal('same plaintext');
        $ct2 = $encryptor->seal('same plaintext');

        static::assertNotSame($ct1, $ct2);
    }

    public function testGenerateKeyProducesValidLength(): void
    {
        $key = Symmetric\generate_key();
        static::assertSame(Symmetric\KEY_BYTES, Byte\length($key->bytes));
    }

    public function testGenerateKeyProducesUniqueKeys(): void
    {
        $key1 = Symmetric\generate_key();
        $key2 = Symmetric\generate_key();

        static::assertNotSame($key1->bytes, $key2->bytes);
    }

    public function testLargePlaintextRoundtrip(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $plaintext = Str\repeat('X', 100_000);
        $ciphertext = $encryptor->seal($plaintext);

        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testBinaryDataRoundtrip(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $plaintext = SecureRandom\bytes(256);
        $ciphertext = $encryptor->seal($plaintext);

        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testCiphertextIncludesNonceAndTag(): void
    {
        $key = Symmetric\generate_key();
        $encryptor = new Symmetric\Encryptor($key);

        $plaintext = 'hello';
        $ciphertext = $encryptor->seal($plaintext);

        $expectedMinLength = Symmetric\NONCE_BYTES + Symmetric\TAG_BYTES + Byte\length($plaintext);
        static::assertSame($expectedMinLength, Byte\length($ciphertext));
    }
}
