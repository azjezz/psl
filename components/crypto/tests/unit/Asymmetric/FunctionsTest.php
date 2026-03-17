<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Asymmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Asymmetric;
use Psl\Crypto\Exception;
use Psl\Str;
use Psl\Str\Byte;

final class FunctionsTest extends TestCase
{
    public function testSealOpenRoundtrip(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $plaintext = 'Sealed message';
        $sealed = Asymmetric\seal($plaintext, $keyPair->publicKey);
        $opened = Asymmetric\open($sealed, $keyPair->secretKey, $keyPair->publicKey);

        static::assertSame($plaintext, $opened);
    }

    public function testSealOpenWithWrongKeyFails(): void
    {
        $keyPair1 = Asymmetric\generate_key_pair();
        $keyPair2 = Asymmetric\generate_key_pair();

        $sealed = Asymmetric\seal('test', $keyPair1->publicKey);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Asymmetric decryption failed.');
        Asymmetric\open($sealed, $keyPair2->secretKey, $keyPair2->publicKey);
    }

    public function testEncryptDecryptRoundtrip(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $plaintext = 'Authenticated encryption';
        $ciphertext = Asymmetric\encrypt($plaintext, $alice->secretKey, $bob->publicKey);
        $decrypted = Asymmetric\decrypt($ciphertext, $bob->secretKey, $alice->publicKey);

        static::assertSame($plaintext, $decrypted);
    }

    public function testEncryptDecryptWithWrongKeyFails(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();
        $eve = Asymmetric\generate_key_pair();

        $ciphertext = Asymmetric\encrypt('secret', $alice->secretKey, $bob->publicKey);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Authenticated asymmetric decryption failed.');
        Asymmetric\decrypt($ciphertext, $eve->secretKey, $alice->publicKey);
    }

    public function testDecryptTooShortCiphertext(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Ciphertext is too short.');
        Asymmetric\decrypt('too-short', $keyPair->secretKey, $keyPair->publicKey);
    }

    public function testGenerateKeyPairProducesValidKeys(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        static::assertSame(Asymmetric\PUBLIC_KEY_BYTES, Byte\length($keyPair->publicKey->bytes));
        static::assertSame(Asymmetric\SECRET_KEY_BYTES, Byte\length($keyPair->secretKey->bytes));
    }

    public function testGenerateKeyPairProducesUniqueKeys(): void
    {
        $kp1 = Asymmetric\generate_key_pair();
        $kp2 = Asymmetric\generate_key_pair();

        static::assertNotSame($kp1->secretKey->bytes, $kp2->secretKey->bytes);
        static::assertNotSame($kp1->publicKey->bytes, $kp2->publicKey->bytes);
    }

    public function testSealProducesDifferentCiphertextEachTime(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $ct1 = Asymmetric\seal('same', $keyPair->publicKey);
        $ct2 = Asymmetric\seal('same', $keyPair->publicKey);

        static::assertNotSame($ct1, $ct2);
    }

    public function testEncryptProducesDifferentCiphertextEachTime(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $ct1 = Asymmetric\encrypt('same', $alice->secretKey, $bob->publicKey);
        $ct2 = Asymmetric\encrypt('same', $alice->secretKey, $bob->publicKey);

        static::assertNotSame($ct1, $ct2);
    }

    public function testSealEmptyPlaintext(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $sealed = Asymmetric\seal('', $keyPair->publicKey);
        $opened = Asymmetric\open($sealed, $keyPair->secretKey, $keyPair->publicKey);

        static::assertSame('', $opened);
    }

    public function testEncryptEmptyPlaintext(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $ciphertext = Asymmetric\encrypt('', $alice->secretKey, $bob->publicKey);
        $decrypted = Asymmetric\decrypt($ciphertext, $bob->secretKey, $alice->publicKey);

        static::assertSame('', $decrypted);
    }

    public function testOpenWithTamperedCiphertextFails(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $sealed = Asymmetric\seal('hello', $keyPair->publicKey);
        $tampered = $sealed;
        $last = Byte\length($tampered) - 1;
        $tampered[$last] = Byte\chr(Byte\ord($tampered[$last]) ^ 0x01);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Asymmetric decryption failed.');
        Asymmetric\open($tampered, $keyPair->secretKey, $keyPair->publicKey);
    }

    public function testDecryptWithTamperedCiphertextFails(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $ciphertext = Asymmetric\encrypt('hello', $alice->secretKey, $bob->publicKey);
        $tampered = $ciphertext;
        $last = Byte\length($tampered) - 1;
        $tampered[$last] = Byte\chr(Byte\ord($tampered[$last]) ^ 0x01);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Authenticated asymmetric decryption failed.');
        Asymmetric\decrypt($tampered, $bob->secretKey, $alice->publicKey);
    }

    public function testSealCiphertextOverhead(): void
    {
        $keyPair = Asymmetric\generate_key_pair();

        $plaintext = 'hello';
        $sealed = Asymmetric\seal($plaintext, $keyPair->publicKey);

        static::assertSame(Byte\length($plaintext) + Asymmetric\SEAL_BYTES, Byte\length($sealed));
    }

    public function testEncryptCiphertextOverhead(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $plaintext = 'hello';
        $ciphertext = Asymmetric\encrypt($plaintext, $alice->secretKey, $bob->publicKey);

        static::assertSame(
            Byte\length($plaintext) + Asymmetric\NONCE_BYTES + Asymmetric\MAC_BYTES,
            Byte\length($ciphertext),
        );
    }

    public function testLargePlaintextRoundtrip(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();

        $plaintext = Str\repeat('X', 50_000);
        $ciphertext = Asymmetric\encrypt($plaintext, $alice->secretKey, $bob->publicKey);
        $decrypted = Asymmetric\decrypt($ciphertext, $bob->secretKey, $alice->publicKey);

        static::assertSame($plaintext, $decrypted);
    }

    public function testDecryptWithUnrelatedKeyPairFails(): void
    {
        $alice = Asymmetric\generate_key_pair();
        $bob = Asymmetric\generate_key_pair();
        $eve = Asymmetric\generate_key_pair();

        $ciphertext = Asymmetric\encrypt('test', $alice->secretKey, $bob->publicKey);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Authenticated asymmetric decryption failed.');
        Asymmetric\decrypt($ciphertext, $eve->secretKey, $eve->publicKey);
    }
}
