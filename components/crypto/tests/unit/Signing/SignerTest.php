<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Signing;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Signing;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Str\Byte;

final class SignerTest extends TestCase
{
    public function testSignVerifyRoundtrip(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $signature = $signer->sign('This is a test message.');

        static::assertTrue($verifier->verify($signature, 'This is a test message.'));
    }

    public function testVerifyWithWrongKeyRejects(): void
    {
        $keyPair1 = Signing\generate_key_pair();
        $keyPair2 = Signing\generate_key_pair();

        $signer = new Signing\Signer($keyPair1->secretKey);
        $verifier = new Signing\Verifier($keyPair2->publicKey);

        $signature = $signer->sign('test message');

        static::assertFalse($verifier->verify($signature, 'test message'));
    }

    public function testVerifyWithModifiedMessageRejects(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $signature = $signer->sign('original message');

        static::assertFalse($verifier->verify($signature, 'modified message'));
    }

    public function testFunctionWrappers(): void
    {
        $keyPair = Signing\generate_key_pair();

        $signature = Signing\sign('Function wrapper test', $keyPair->secretKey);

        static::assertTrue(Signing\verify($signature, 'Function wrapper test', $keyPair->publicKey));
    }

    public function testSignatureIsDeterministic(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);

        $sig1 = $signer->sign('deterministic test');
        $sig2 = $signer->sign('deterministic test');

        static::assertSame($sig1->bytes, $sig2->bytes);
    }

    public function testSignEmptyMessage(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $signature = $signer->sign('');
        static::assertTrue($verifier->verify($signature, ''));
    }

    public function testEmptyMessageSignatureRejectsNonEmpty(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $signature = $signer->sign('');
        static::assertFalse($verifier->verify($signature, 'not-empty'));
    }

    public function testGenerateKeyPairProducesValidKeys(): void
    {
        $keyPair = Signing\generate_key_pair();

        static::assertSame(Signing\SECRET_KEY_BYTES, Byte\length($keyPair->secretKey->bytes));
        static::assertSame(Signing\PUBLIC_KEY_BYTES, Byte\length($keyPair->publicKey->bytes));
    }

    public function testGenerateKeyPairProducesUniqueKeys(): void
    {
        $kp1 = Signing\generate_key_pair();
        $kp2 = Signing\generate_key_pair();

        static::assertNotSame($kp1->secretKey->bytes, $kp2->secretKey->bytes);
        static::assertNotSame($kp1->publicKey->bytes, $kp2->publicKey->bytes);
    }

    public function testSignatureLength(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);

        $signature = $signer->sign('test');
        static::assertSame(Signing\SIGNATURE_BYTES, Byte\length($signature->bytes));
    }

    public function testSignLargeMessage(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $message = Str\repeat('X', 100_000);
        $signature = $signer->sign($message);

        static::assertTrue($verifier->verify($signature, $message));
    }

    public function testSignBinaryData(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);
        $verifier = new Signing\Verifier($keyPair->publicKey);

        $message = SecureRandom\bytes(512);
        $signature = $signer->sign($message);

        static::assertTrue($verifier->verify($signature, $message));
    }

    public function testFunctionWrapperAndClassProduceSameSignature(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);

        $classSig = $signer->sign('consistency');
        $funcSig = Signing\sign('consistency', $keyPair->secretKey);

        static::assertSame($classSig->bytes, $funcSig->bytes);
    }

    public function testFunctionVerifyWithClassSign(): void
    {
        $keyPair = Signing\generate_key_pair();
        $signer = new Signing\Signer($keyPair->secretKey);

        $signature = $signer->sign('cross-test');
        static::assertTrue(Signing\verify($signature, 'cross-test', $keyPair->publicKey));
    }
}
