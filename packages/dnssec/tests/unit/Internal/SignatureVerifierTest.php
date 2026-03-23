<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNSSEC\Internal\SignatureVerifier;
use Psl\Str;
use Psl\Str\Byte;
use RuntimeException;

use function is_string;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function openssl_sign;
use function sodium_crypto_sign_detached;
use function sodium_crypto_sign_keypair;
use function sodium_crypto_sign_publickey;
use function sodium_crypto_sign_secretkey;

use const OPENSSL_ALGO_SHA256;
use const OPENSSL_ALGO_SHA384;
use const OPENSSL_ALGO_SHA512;
use const OPENSSL_KEYTYPE_EC;
use const OPENSSL_KEYTYPE_RSA;

final class SignatureVerifierTest extends TestCase
{
    public function testVerifyRsaSha256(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $data = 'test data for RSASHA256';
        $signature = self::opensslSign($data, $key, OPENSSL_ALGO_SHA256);

        static::assertTrue(SignatureVerifier::verify(Algorithm::RSASHA256, $signature, $data, $rawKey));
    }

    public function testVerifyRsaSha512(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $data = 'test data for RSASHA512';
        $signature = self::opensslSign($data, $key, OPENSSL_ALGO_SHA512);

        static::assertTrue(SignatureVerifier::verify(Algorithm::RSASHA512, $signature, $data, $rawKey));
    }

    public function testVerifyRsaSha256InvalidSignature(): void
    {
        [$rawKey] = self::generateRsaKey();

        static::assertFalse(SignatureVerifier::verify(Algorithm::RSASHA256, 'bad-signature', 'data', $rawKey));
    }

    public function testVerifyEcdsaP256Sha256(): void
    {
        [$rawKey, $key] = self::generateEcKey('prime256v1');

        $data = 'test data for ECDSAP256SHA256';
        $derSignature = self::opensslSign($data, $key, OPENSSL_ALGO_SHA256);
        $rawSig = self::derSignatureToRaw($derSignature, 32);

        static::assertTrue(SignatureVerifier::verify(Algorithm::ECDSAP256SHA256, $rawSig, $data, $rawKey));
    }

    public function testVerifyEcdsaP384Sha384(): void
    {
        [$rawKey, $key] = self::generateEcKey('secp384r1');

        $data = 'test data for ECDSAP384SHA384';
        $derSignature = self::opensslSign($data, $key, OPENSSL_ALGO_SHA384);
        $rawSig = self::derSignatureToRaw($derSignature, 48);

        static::assertTrue(SignatureVerifier::verify(Algorithm::ECDSAP384SHA384, $rawSig, $data, $rawKey));
    }

    public function testVerifyEd25519(): void
    {
        $keypair = sodium_crypto_sign_keypair();
        $publicKey = sodium_crypto_sign_publickey($keypair);
        $secretKey = sodium_crypto_sign_secretkey($keypair);

        $data = 'test data for Ed25519';
        $signature = sodium_crypto_sign_detached($data, $secretKey);

        static::assertTrue(SignatureVerifier::verify(Algorithm::ED25519, $signature, $data, $publicKey));
    }

    public function testVerifyEd25519InvalidSignature(): void
    {
        $keypair = sodium_crypto_sign_keypair();
        $publicKey = sodium_crypto_sign_publickey($keypair);

        $badSignature = Str\repeat("\x00", 64);

        static::assertFalse(SignatureVerifier::verify(Algorithm::ED25519, $badSignature, 'data', $publicKey));
    }

    /** @return array{string, OpenSSLAsymmetricKey} */
    private static function generateRsaKey(): array
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($key === false) {
            throw new RuntimeException('Failed to generate RSA key pair');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            throw new RuntimeException('Failed to get RSA key details');
        }

        if (!isset($details['rsa']['e']) || !is_string($details['rsa']['e'])) {
            throw new RuntimeException('Missing RSA exponent in key details');
        }

        if (!isset($details['rsa']['n']) || !is_string($details['rsa']['n'])) {
            throw new RuntimeException('Missing RSA modulus in key details');
        }

        $exponent = $details['rsa']['e'];
        $modulus = $details['rsa']['n'];
        $expLen = Byte\length($exponent);

        if ($expLen < 256) {
            return [Byte\chr($expLen) . $exponent . $modulus, $key];
        }

        return ["\x00" . Byte\chr(($expLen >> 8) & 0xFF) . Byte\chr($expLen & 0xFF) . $exponent . $modulus, $key];
    }

    /** @return array{string, OpenSSLAsymmetricKey} */
    private static function generateEcKey(string $curveName): array
    {
        $coordinateSize = match ($curveName) {
            'prime256v1' => 32,
            'secp384r1' => 48,
            default => throw new RuntimeException('Unsupported curve: ' . $curveName),
        };

        $key = openssl_pkey_new(['curve_name' => $curveName, 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        if ($key === false) {
            throw new RuntimeException('Failed to generate EC key pair for curve ' . $curveName);
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            throw new RuntimeException('Failed to get EC key details');
        }

        if (!isset($details['ec']['x']) || !is_string($details['ec']['x'])) {
            throw new RuntimeException('Missing EC x coordinate in key details');
        }

        if (!isset($details['ec']['y']) || !is_string($details['ec']['y'])) {
            throw new RuntimeException('Missing EC y coordinate in key details');
        }

        $x = Byte\pad_left($details['ec']['x'], $coordinateSize, "\x00");
        $y = Byte\pad_left($details['ec']['y'], $coordinateSize, "\x00");

        return [$x . $y, $key];
    }

    private static function opensslSign(string $data, OpenSSLAsymmetricKey $key, int $algorithm): string
    {
        $signature = null;
        $result = openssl_sign($data, $signature, $key, $algorithm);
        if (!$result) {
            throw new RuntimeException('Failed to sign data with OpenSSL');
        }

        return $signature;
    }

    /** @param non-negative-int $coordinateSize */
    private static function derSignatureToRaw(string $der, int $coordinateSize): string
    {
        $offset = 2;

        $offset++;
        $rLen = Byte\ord($der[$offset]);
        $offset++;
        $r = Byte\slice($der, $offset, $rLen);
        $offset += $rLen;

        $offset++;
        $sLen = Byte\ord($der[$offset]);
        $offset++;
        $s = Byte\slice($der, $offset, $sLen);

        $r = self::padCoordinate($r, $coordinateSize);
        $s = self::padCoordinate($s, $coordinateSize);

        return $r . $s;
    }

    /** @param non-negative-int $size */
    private static function padCoordinate(string $value, int $size): string
    {
        while (Byte\length($value) > $size && $value[0] === "\x00") {
            $value = Byte\slice($value, 1);
        }

        return Byte\pad_left($value, $size, "\x00");
    }
}
