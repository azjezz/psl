<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\KeyExchange;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\KeyExchange;
use Psl\Str\Byte;

final class FunctionsTest extends TestCase
{
    public function testAgreeProducesSameSecretFromBothSides(): void
    {
        $alice = KeyExchange\generate_key_pair();
        $bob = KeyExchange\generate_key_pair();

        $aliceShared = KeyExchange\agree($alice->secretKey, $bob->publicKey);
        $bobShared = KeyExchange\agree($bob->secretKey, $alice->publicKey);

        static::assertSame($aliceShared->bytes, $bobShared->bytes);
    }

    public function testDifferentKeyPairsProduceDifferentSecrets(): void
    {
        $alice = KeyExchange\generate_key_pair();
        $bob = KeyExchange\generate_key_pair();
        $eve = KeyExchange\generate_key_pair();

        $aliceBob = KeyExchange\agree($alice->secretKey, $bob->publicKey);
        $aliceEve = KeyExchange\agree($alice->secretKey, $eve->publicKey);

        static::assertNotSame($aliceBob->bytes, $aliceEve->bytes);
    }

    public function testGenerateKeyPairProducesValidKeys(): void
    {
        $keyPair = KeyExchange\generate_key_pair();

        static::assertSame(KeyExchange\PUBLIC_KEY_BYTES, Byte\length($keyPair->publicKey->bytes));
        static::assertSame(KeyExchange\SECRET_KEY_BYTES, Byte\length($keyPair->secretKey->bytes));
    }

    public function testGenerateKeyPairProducesUniqueKeys(): void
    {
        $kp1 = KeyExchange\generate_key_pair();
        $kp2 = KeyExchange\generate_key_pair();

        static::assertNotSame($kp1->secretKey->bytes, $kp2->secretKey->bytes);
        static::assertNotSame($kp1->publicKey->bytes, $kp2->publicKey->bytes);
    }

    public function testAgreeIsDeterministic(): void
    {
        $alice = KeyExchange\generate_key_pair();
        $bob = KeyExchange\generate_key_pair();

        $shared1 = KeyExchange\agree($alice->secretKey, $bob->publicKey);
        $shared2 = KeyExchange\agree($alice->secretKey, $bob->publicKey);

        static::assertSame($shared1->bytes, $shared2->bytes);
    }

    public function testSharedSecretLength(): void
    {
        $alice = KeyExchange\generate_key_pair();
        $bob = KeyExchange\generate_key_pair();

        $shared = KeyExchange\agree($alice->secretKey, $bob->publicKey);

        static::assertSame(KeyExchange\SHARED_SECRET_BYTES, Byte\length($shared->bytes));
    }

    public function testThreePartyKeyExchange(): void
    {
        $alice = KeyExchange\generate_key_pair();
        $bob = KeyExchange\generate_key_pair();
        $charlie = KeyExchange\generate_key_pair();

        $aliceBob = KeyExchange\agree($alice->secretKey, $bob->publicKey);
        $aliceCharlie = KeyExchange\agree($alice->secretKey, $charlie->publicKey);
        $bobCharlie = KeyExchange\agree($bob->secretKey, $charlie->publicKey);

        static::assertNotSame($aliceBob->bytes, $aliceCharlie->bytes);
        static::assertNotSame($aliceBob->bytes, $bobCharlie->bytes);
        static::assertNotSame($aliceCharlie->bytes, $bobCharlie->bytes);
    }
}
