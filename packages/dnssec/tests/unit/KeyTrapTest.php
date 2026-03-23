<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Binary\Writer;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\DSRecord;
use Psl\DNS\Record\RecordInterface;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNSSEC\ChainFailure;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\DNSSEC\SecureResolver;
use Psl\DNSSEC\TrustAnchor;
use Psl\DNSSEC\TrustChainResolver;
use Psl\DNSSEC\TrustChainResolverInterface;
use Psl\DNSSEC\TrustChainResult;
use Psl\DNSSEC\TrustChainStatus;
use Psl\IP\Address;
use Psl\Str\Byte;
use RuntimeException;

use function hash;
use function openssl_pkey_get_details;
use function openssl_pkey_new;

use const OPENSSL_KEYTYPE_RSA;

final class KeyTrapTest extends TestCase
{
    public function testRejectsResponseWithExcessiveDnskeyRecords(): void
    {
        $dnskeys = [];
        for ($i = 0; $i < 50; $i++) {
            $dnskeys[] = new DNSKEYRecord(
                'example.com',
                Duration::seconds(3600),
                257,
                3,
                Algorithm::RSASHA256,
                self::generateRsaRfc3110Key(),
            );
        }

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            12_345,
            'example.com',
            'dummy-signature',
        );

        $aRecord = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $inner = self::createMockResolver([$aRecord, $rrsig]);
        $trustChain = self::createMockTrustChainResolver(['example.com' => $dnskeys]);
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(InvalidProofException::class);

        $resolver->query('example.com', RecordType::A);
    }

    public function testRejectsResponseWithExcessiveRrsigRecords(): void
    {
        $rfc3110Key = self::generateRsaRfc3110Key();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

        $now = Timestamp::now()->getSeconds();
        $rrsigs = [];
        for ($i = 0; $i < 50; $i++) {
            $rrsigs[] = new RRSIGRecord(
                'example.com',
                Duration::seconds(3600),
                RecordType::A,
                Algorithm::RSASHA256,
                2,
                300,
                $now + 86_400,
                $now - 86_400,
                $keyTag,
                'example.com',
                'invalid-signature-' . $i,
            );
        }

        $aRecord = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $inner = self::createMockResolver([$aRecord, ...$rrsigs]);
        $trustChain = self::createMockTrustChainResolver(['example.com' => [$dnskey]]);
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(InvalidProofException::class);

        $resolver->query('example.com', RecordType::A);
    }

    public function testRejectsResponseWithManyDnskeysAndManyRrsigs(): void
    {
        $dnskeys = [];
        for ($i = 0; $i < 20; $i++) {
            $dnskeys[] = new DNSKEYRecord(
                'example.com',
                Duration::seconds(3600),
                257,
                3,
                Algorithm::RSASHA256,
                self::generateRsaRfc3110Key(),
            );
        }

        $now = Timestamp::now()->getSeconds();
        $rrsigs = [];
        for ($i = 0; $i < 20; $i++) {
            $rrsigs[] = new RRSIGRecord(
                'example.com',
                Duration::seconds(3600),
                RecordType::A,
                Algorithm::RSASHA256,
                2,
                300,
                $now + 86_400,
                $now - 86_400,
                KeyTag::compute($dnskeys[$i]),
                'example.com',
                'invalid-signature-' . $i,
            );
        }

        $aRecord = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $inner = self::createMockResolver([$aRecord, ...$rrsigs]);
        $trustChain = self::createMockTrustChainResolver(['example.com' => $dnskeys]);
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(InvalidProofException::class);

        $resolver->query('example.com', RecordType::A);
    }

    public function testTrustChainRejectsExcessiveDnskeysInSelfSignedVerification(): void
    {
        $extraDnskeys = [];
        for ($i = 0; $i < 9; $i++) {
            $extraDnskeys[] = new DNSKEYRecord(
                '.',
                Duration::seconds(3600),
                257,
                3,
                Algorithm::RSASHA256,
                self::generateRsaRfc3110Key(),
            );
        }

        $resolver = self::createRootChainResolver($extraDnskeys);

        $result = $resolver->resolve('');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ResourceExhaustion, $result->failure);
    }

    public function testTrustChainAcceptsExactlyEightDnskeysInSelfSignedVerification(): void
    {
        $extraDnskeys = [];
        for ($i = 0; $i < 7; $i++) {
            $extraDnskeys[] = new DNSKEYRecord(
                '.',
                Duration::seconds(3600),
                257,
                3,
                Algorithm::RSASHA256,
                self::generateRsaRfc3110Key(),
            );
        }

        $resolver = self::createRootChainResolver($extraDnskeys);

        $result = $resolver->resolve('');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::SignatureVerificationFailed, $result->failure);
    }

    public function testTrustChainRejectsExcessiveRrsigsInSelfSignedVerification(): void
    {
        $resolver = self::createRootChainResolver([], 10);

        $result = $resolver->resolve('');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ResourceExhaustion, $result->failure);
    }

    public function testTrustChainAcceptsExactlyEightRrsigsInSelfSignedVerification(): void
    {
        $resolver = self::createRootChainResolver([], 8);

        $result = $resolver->resolve('');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::SignatureVerificationFailed, $result->failure);
    }

    /**
     * @param list<DNSKEYRecord> $extraDnskeys
     */
    private static function createRootChainResolver(array $extraDnskeys = [], int $rrsigCount = 1): TrustChainResolver
    {
        $rootKey = self::generateRsaRfc3110Key();
        $rootDnskey = new DNSKEYRecord('.', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rootKey);
        $rootKeyTag = KeyTag::compute($rootDnskey);

        $ownerWire = Encoder::encodeName('');
        $dnskeyRdata = new Writer()
            ->u16(257)
            ->u8(3)
            ->u8(8)
            ->bytes($rootKey)
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

        $dnskeys = [$rootDnskey, ...$extraDnskeys];

        $now = Timestamp::now()->getSeconds();
        $rrsigs = [];
        for ($i = 0; $i < $rrsigCount; $i++) {
            $rrsigs[] = new RRSIGRecord(
                '.',
                Duration::seconds(3600),
                RecordType::DNSKEY,
                Algorithm::RSASHA256,
                0,
                3600,
                $now + 86_400,
                $now - 86_400,
                $rootKeyTag,
                '',
                'dummy-signature-' . $i,
            );
        }

        /** @var list<RecordInterface> $answers */
        $answers = [...$dnskeys, ...$rrsigs];

        $inner = new class($answers) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            /** @param list<RecordInterface> $answers */
            public function __construct(
                private readonly array $answers,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                if ($type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, $this->answers, [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        return new TrustChainResolver($inner, $anchor);
    }

    /**
     * @param list<RecordInterface> $answers
     */
    private static function createMockResolver(array $answers): ResolverInterface
    {
        return new class($answers) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            /** @param list<RecordInterface> $answers */
            public function __construct(
                private readonly array $answers,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, $this->answers, [], []);
            }
        };
    }

    /**
     * @param array<string, list<DNSKEYRecord>> $zoneKeys
     */
    private static function createMockTrustChainResolver(array $zoneKeys): TrustChainResolverInterface
    {
        return new class($zoneKeys) implements TrustChainResolverInterface {
            /** @param array<string, list<DNSKEYRecord>> $zoneKeys */
            public function __construct(
                private readonly array $zoneKeys,
            ) {}

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Secure, $this->zoneKeys[$zone] ?? []);
            }
        };
    }

    private static function generateRsaRfc3110Key(): string
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
            return Byte\chr($expLen) . $exponent . $modulus;
        }

        return "\x00" . Byte\chr(($expLen >> 8) & 0xFF) . Byte\chr($expLen & 0xFF) . $exponent . $modulus;
    }
}
