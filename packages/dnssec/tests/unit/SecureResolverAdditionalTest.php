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
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\NSECRecord;
use Psl\DNS\Record\RecordInterface;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNSSEC\ChainFailure;
use Psl\DNSSEC\Exception\BrokenTrustChainException;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\DNSSEC\SecureResolver;
use Psl\DNSSEC\TrustChainResolverInterface;
use Psl\DNSSEC\TrustChainResult;
use Psl\DNSSEC\TrustChainStatus;
use Psl\IP\Address;
use Psl\Str\Byte;
use RuntimeException;

use function is_string;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function openssl_sign;

use const OPENSSL_ALGO_SHA256;
use const OPENSSL_KEYTYPE_RSA;

final class SecureResolverAdditionalTest extends TestCase
{
    public function testPositiveResponseWithNoRrsigAndBogusChainThrows(): void
    {
        $answers = [
            new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4')),
        ];

        $inner = self::createMockResolver($answers);
        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDnskey);
            }
        };
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(BrokenTrustChainException::class);
        $this->expectExceptionMessage('MissingDnskey');

        $resolver->query('example.com', RecordType::A);
    }

    public function testPositiveResponseWithNoRrsigAndInsecureChainReturnsAdFalse(): void
    {
        $answers = [
            new ARecord('insecure.example.com', Duration::seconds(300), Address::v4('1.2.3.4')),
        ];

        $inner = self::createMockResolver($answers);
        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('insecure.example.com', RecordType::A);

        static::assertFalse($response->authenticatedData);
        static::assertCount(1, $response->answers);
    }

    public function testPositiveResponseWithRrsigAndBogusSignerChainThrows(): void
    {
        $rfc3110Key = self::generateRsaRfc3110Key();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

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
            $keyTag,
            'example.com',
            'dummy-sig',
        );

        $answers = [
            new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4')),
            $rrsig,
        ];

        $inner = self::createMockResolver($answers);
        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::SignatureVerificationFailed);
            }
        };
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(BrokenTrustChainException::class);
        $this->expectExceptionMessage('SignatureVerificationFailed');

        $resolver->query('example.com', RecordType::A);
    }

    public function testPositiveResponseWithRrsigAndInsecureSignerReturnsAdFalse(): void
    {
        $rfc3110Key = self::generateRsaRfc3110Key();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

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
            $keyTag,
            'example.com',
            'dummy-sig',
        );

        $answers = [
            new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4')),
            $rrsig,
        ];

        $inner = self::createMockResolver($answers);
        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertFalse($response->authenticatedData);
        static::assertCount(2, $response->answers);
    }

    public function testNegativeResponseWithNsecAndBogusChainThrows(): void
    {
        $nsec = new NSECRecord('example.com', Duration::seconds(3600), 'mail.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        $rfc3110Key = self::generateRsaRfc3110Key();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            2,
            3600,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            'dummy-sig',
        );

        $inner = new class($nsec, $rrsig) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly NSECRecord $nsec,
                private readonly RRSIGRecord $rrsig,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, [], [$this->nsec, $this->rrsig], []);
            }
        };

        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::ChainBroken);
            }
        };

        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(BrokenTrustChainException::class);
        $this->expectExceptionMessage('ChainBroken');

        $resolver->query('example.com', RecordType::AAAA);
    }

    public function testNegativeResponseWithNsecAndInsecureChainReturnsAdFalse(): void
    {
        $nsec = new NSECRecord('example.com', Duration::seconds(3600), 'mail.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        $rfc3110Key = self::generateRsaRfc3110Key();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            2,
            3600,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            'dummy-sig',
        );

        $inner = new class($nsec, $rrsig) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly NSECRecord $nsec,
                private readonly RRSIGRecord $rrsig,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, [], [$this->nsec, $this->rrsig], []);
            }
        };

        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };

        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('example.com', RecordType::AAAA);

        static::assertFalse($response->authenticatedData);
    }

    public function testNegativeResponseWithNoNsecNoRrsigAndBogusChainThrows(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NonExistentDomain, [], [], []);
            }
        };

        $trustChain = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDs);
            }
        };

        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(BrokenTrustChainException::class);
        $this->expectExceptionMessage('MissingDs');

        $resolver->query('bogus.example.com', RecordType::A);
    }

    public function testNegativeResponseValidatesNxdomainWithNsecProof(): void
    {
        [$privateKey, $rfc3110Key] = self::generateRsaKeyPair();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

        $nsec1 = new NSECRecord(
            'alpha.example.com',
            Duration::seconds(3600),
            'gamma.example.com',
            [RecordType::A, RecordType::RRSIG, RecordType::NSEC],
        );

        $nsec2 = new NSECRecord('example.com', Duration::seconds(3600), 'alpha.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $nsec1SignedData = self::buildNsecSignedData($nsec1, $keyTag, 'example.com', $expiration, $inception);
        $nsec1Signature = null;
        openssl_sign($nsec1SignedData, $nsec1Signature, $privateKey, OPENSSL_ALGO_SHA256);

        $nsec1Rrsig = new RRSIGRecord(
            'alpha.example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            3,
            3600,
            $expiration,
            $inception,
            $keyTag,
            'example.com',
            $nsec1Signature,
        );

        $nsec2SignedData = self::buildNsecSignedData($nsec2, $keyTag, 'example.com', $expiration, $inception);
        $nsec2Signature = null;
        openssl_sign($nsec2SignedData, $nsec2Signature, $privateKey, OPENSSL_ALGO_SHA256);

        $nsec2Rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            2,
            3600,
            $expiration,
            $inception,
            $keyTag,
            'example.com',
            $nsec2Signature,
        );

        $inner = new class($nsec1, $nsec2, $nsec1Rrsig, $nsec2Rrsig) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly NSECRecord $nsec1,
                private readonly NSECRecord $nsec2,
                private readonly RRSIGRecord $rrsig1,
                private readonly RRSIGRecord $rrsig2,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(
                    1,
                    ResponseCode::NonExistentDomain,
                    [],
                    [$this->nsec1, $this->nsec2, $this->rrsig1, $this->rrsig2],
                    [],
                );
            }
        };

        $trustChain = self::createMockTrustChainResolver(['example.com' => [$dnskey]]);
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('beta.example.com', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
    }

    public function testNegativeResponseNodataWithValidSignedProof(): void
    {
        [$privateKey, $rfc3110Key] = self::generateRsaKeyPair();
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rfc3110Key);
        $keyTag = KeyTag::compute($dnskey);

        $nsec = new NSECRecord('example.com', Duration::seconds(3600), 'mail.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $nsecSignedData = self::buildNsecSignedData($nsec, $keyTag, 'example.com', $expiration, $inception);
        $nsecSignature = null;
        openssl_sign($nsecSignedData, $nsecSignature, $privateKey, OPENSSL_ALGO_SHA256);

        $nsecRrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            2,
            3600,
            $expiration,
            $inception,
            $keyTag,
            'example.com',
            $nsecSignature,
        );

        $inner = new class($nsec, $nsecRrsig) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly NSECRecord $nsec,
                private readonly RRSIGRecord $rrsig,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, [], [$this->nsec, $this->rrsig], []);
            }
        };

        $trustChain = self::createMockTrustChainResolver(['example.com' => [$dnskey]]);
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('example.com', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
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

    private static function buildNsecSignedData(
        NSECRecord $nsec,
        int $keyTag,
        string $signer,
        int $expiration,
        int $inception,
    ): string {
        $signerWire = Encoder::encodeName($signer);

        $labels = \count(\array_filter(
            \explode('.', Byte\lowercase($nsec->name)),
            static fn(string $l): bool => $l !== '',
        ));

        $rrsigPrefix = new Writer()
            ->u16(RecordType::NSEC->value)
            ->u8(8)
            ->u8($labels)
            ->u32(3600)
            ->u32($expiration)
            ->u32($inception)
            ->u16($keyTag)
            ->bytes($signerWire)
            ->toString();

        $ownerWire = Encoder::encodeName(Byte\lowercase($nsec->name));
        $rdata =
            Encoder::encodeName(Byte\lowercase($nsec->nextDomainName))
            . \Psl\DNS\Internal\TypeBitmap::encode($nsec->types);

        $canonicalRr = new Writer()
            ->bytes($ownerWire)
            ->u16(RecordType::NSEC->value)
            ->u16(1)
            ->u32(3600)
            ->u16(Byte\length($rdata))
            ->bytes($rdata)
            ->toString();

        return $rrsigPrefix . $canonicalRr;
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
            return [$key, Byte\chr($expLen) . $exponent . $modulus];
        }

        return [$key, "\x00" . Byte\chr(($expLen >> 8) & 0xFF) . Byte\chr($expLen & 0xFF) . $exponent . $modulus];
    }

    private static function generateRsaRfc3110Key(): string
    {
        [, $key] = self::generateRsaKeyPair();

        return $key;
    }
}
