<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Asymmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Asymmetric;
use Psl\Crypto\EncryptorInterface;
use Psl\Crypto\Exception;
use Psl\SecureRandom;
use Psl\Str;

use function Psl\Str\Byte\length;

final class EncryptorTest extends TestCase
{
    public function testSealAndOpenRoundtrip(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $plaintext = 'Hello, asymmetric encryption!';
        $ciphertext = $encryptor->seal($plaintext);

        static::assertNotSame($plaintext, $ciphertext);
        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testOpenWithWrongKeyFails(): void
    {
        $keyPair1 = Asymmetric\generate_key_pair();
        $keyPair2 = Asymmetric\generate_key_pair();

        $encryptor1 = new Asymmetric\Encryptor($keyPair1->secretKey, $keyPair1->publicKey);
        $encryptor2 = new Asymmetric\Encryptor($keyPair2->secretKey, $keyPair2->publicKey);

        $ciphertext = $encryptor1->seal('secret message');

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Asymmetric decryption failed.');
        $encryptor2->open($ciphertext);
    }

    public function testEmptyPlaintext(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $ciphertext = $encryptor->seal('');
        static::assertSame('', $encryptor->open($ciphertext));
    }

    public function testSealProducesDifferentCiphertextEachTime(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $ct1 = $encryptor->seal('same message');
        $ct2 = $encryptor->seal('same message');

        static::assertNotSame($ct1, $ct2);
    }

    public function testImplementsEncryptorInterface(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        static::assertInstanceOf(EncryptorInterface::class, $encryptor);
    }

    public function testOpenWithTamperedCiphertextFails(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $ciphertext = $encryptor->seal('hello');
        $tampered = $ciphertext;
        $last = length($tampered) - 1;
        $tampered[$last] = \Psl\Str\Byte\chr(\Psl\Str\Byte\ord($tampered[$last]) ^ 0x01);

        $this->expectException(Exception\DecryptionException::class);
        $this->expectExceptionMessage('Asymmetric decryption failed.');
        $encryptor->open($tampered);
    }

    public function testLargePlaintext(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $plaintext = Str\repeat('A', 50_000);
        $ciphertext = $encryptor->seal($plaintext);

        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testBinaryDataRoundtrip(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $plaintext = SecureRandom\bytes(256);
        $ciphertext = $encryptor->seal($plaintext);

        static::assertSame($plaintext, $encryptor->open($ciphertext));
    }

    public function testSealedByFunctionOpenedByEncryptor(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $sealed = Asymmetric\seal('cross-compat', $keyPair->publicKey);

        static::assertSame('cross-compat', $encryptor->open($sealed));
    }

    public function testSealedByEncryptorOpenedByFunction(): void
    {
        $keyPair = Asymmetric\generate_key_pair();
        $encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

        $sealed = $encryptor->seal('cross-compat');

        static::assertSame('cross-compat', Asymmetric\open($sealed, $keyPair->secretKey, $keyPair->publicKey));
    }
}
