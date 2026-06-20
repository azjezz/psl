<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit;

use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Binary\Writer;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\DSRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNSSEC\ChainFailure;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\DNSSEC\TrustAnchor;
use Psl\DNSSEC\TrustChainResolver;
use Psl\DNSSEC\TrustChainStatus;
use Psl\Iter;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use RuntimeException;

use function hash;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function openssl_sign;
use function rtrim;

use const OPENSSL_ALGO_SHA256;
use const OPENSSL_KEYTYPE_RSA;

final class DsRrsigBypassTest extends TestCase
{
    public function testResolveRejectsDsRecordsWithoutRrsig(): void
    {
        [$rootPrivateKey, $rootRfc3110Key] = self::generateRsaKeyPair();
        $rootDnskey = new DNSKEYRecord('.', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rootRfc3110Key);
        $rootKeyTag = KeyTag::compute($rootDnskey);

        $ownerWire = Encoder::encodeName('');
        $dnskeyRdata = new Writer()
            ->u16(257)
            ->u8(3)
            ->u8(8)
            ->bytes($rootRfc3110Key)
            ->toString();
        $digestHex = hash('sha256', $ownerWire . $dnskeyRdata);

        $anchor = new TrustAnchor([
            new DSRecord(
                '',
                Duration::seconds(0),
                $rootKeyTag,
                Algorithm::RSASHA256,
                DigestAlgorithm::SHA256,
                $digestHex,
            ),
        ]);

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $childOwnerWire = Encoder::encodeName('com');
        $childDnskeyRdata = new Writer()
            ->u16(257)
            ->u8(3)
            ->u8(8)
            ->bytes($childRfc3110Key)
            ->toString();
        $childDigestHex = hash('sha256', $childOwnerWire . $childDnskeyRdata);

        $childDs = new DSRecord(
            'com',
            Duration::seconds(3600),
            $childKeyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            $childDigestHex,
        );

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $rootSignedData = self::buildDnskeySignedData([$rootDnskey], $rootKeyTag, '', $expiration, $inception);
        $rootSignature = null;
        openssl_sign($rootSignedData, $rootSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $rootRrsig = new RRSIGRecord(
            '.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            0,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $rootSignature,
        );

        $childDnskeyRrsig = new RRSIGRecord(
            'com',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            'dummy-will-not-be-verified',
        );

        $inner = new class($rootDnskey, $rootRrsig, $childDnskey, $childDnskeyRrsig, $childDs) implements
            ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly DNSKEYRecord $rootDnskey,
                private readonly RRSIGRecord $rootRrsig,
                private readonly DNSKEYRecord $childDnskey,
                private readonly RRSIGRecord $childDnskeyRrsig,
                private readonly DSRecord $childDs,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                // @mago-expect lint:no-shorthand-ternary
                $name = rtrim($name, '.') ?: '.';
                if ($name === '.' && $type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, [$this->rootDnskey, $this->rootRrsig], [], []);
                }

                if ($name === 'com' && $type === RecordType::DNSKEY) {
                    return new Response(
                        1,
                        ResponseCode::NoError,
                        [$this->childDnskey, $this->childDnskeyRrsig],
                        [],
                        [],
                    );
                }

                if ($name === 'com' && $type === RecordType::DS) {
                    return new Response(1, ResponseCode::NoError, [$this->childDs], [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        $resolver = new TrustChainResolver($inner, $anchor);

        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::UnsignedDs, $result->failure);
    }

    /**
     * @param list<DNSKEYRecord> $dnskeys
     */
    private static function buildDnskeySignedData(
        array $dnskeys,
        int $keyTag,
        string $signer,
        int $expiration,
        int $inception,
    ): string {
        $signerWire = Encoder::encodeName($signer);
        $rrsigPrefix = new Writer()
            ->u16(RecordType::DNSKEY->value)
            ->u8(8)
            ->u8(Byte\length($signer) > 0 ? Iter\count::<string>(Byte\split($signer, '.')) : 0)
            ->u32(3600)
            ->u32($expiration)
            ->u32($inception)
            ->u16($keyTag)
            ->bytes($signerWire)
            ->toString();

        $canonicalRrs = [];
        foreach ($dnskeys as $dnskey) {
            $ownerWire = Encoder::encodeName($signer === '' ? '.' : $signer . '.');
            $rdata = new Writer()
                ->u16($dnskey->flags)
                ->u8($dnskey->protocol)
                ->u8($dnskey->algorithm->value)
                ->bytes($dnskey->publicKey)
                ->toString();

            $canonicalRrs[] = new Writer()
                ->bytes($ownerWire)
                ->u16(RecordType::DNSKEY->value)
                ->u16(1)
                ->u32(3600)
                ->u16(Byte\length($rdata))
                ->bytes($rdata)
                ->toString();
        }

        $canonicalRrs = Vec\sort::<string>($canonicalRrs);

        return $rrsigPrefix . Str\join($canonicalRrs, '');
    }

    /**
     * @return array{OpenSSLAsymmetricKey, string}
     */
    private static function generateRsaKeyPair(): array
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($key === false) {
            throw new RuntimeException('Failed to generate RSA key pair');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            throw new RuntimeException('Failed to get RSA key details');
        }

        /** @var string $exponent */
        $exponent = $details['rsa']['e'];
        /** @var string $modulus */
        $modulus = $details['rsa']['n'];
        $expLen = Byte\length($exponent);

        if ($expLen < 256) {
            return [$key, Byte\chr($expLen) . $exponent . $modulus];
        }

        return [$key, "\x00" . Byte\chr(($expLen >> 8) & 0xFF) . Byte\chr($expLen & 0xFF) . $exponent . $modulus];
    }
}
