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
use Psl\DNS\Record\RecordInterface;
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
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use RuntimeException;

use function array_filter;
use function count;
use function explode;
use function hash;
use function hex2bin;
use function is_string;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function openssl_sign;
use function rtrim;

use const OPENSSL_ALGO_SHA256;
use const OPENSSL_KEYTYPE_RSA;

final class TrustChainResolverAdditionalTest extends TestCase
{
    public function testResolveChildZoneWithDsButNoRrsigReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::UnsignedDs, $result->failure);
    }

    public function testResolveChildZoneWithDsRrsigFromUnknownParentReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $now + 86_400,
            $now - 86_400,
            99_999,
            'unknown-parent.',
            'dummy-signature',
        );

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
            $dsRrsig,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ChainBroken, $result->failure);
    }

    public function testResolveChildZoneWithNoDnskeyReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => []],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::MissingDnskey, $result->failure);
    }

    public function testResolveChildZoneWithDnskeyButNoRrsigReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => [$childDnskey]],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::UnsignedDnskey, $result->failure);
    }

    public function testResolveChildZoneWithDsMismatchReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = new DSRecord(
            'com.',
            Duration::seconds(3600),
            $childKeyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            'deadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeef',
        );

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $childDnskeyRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            'dummy',
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => [$childDnskey]],
            childRrsigs: ['com' => $childDnskeyRrsig],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ChainBroken, $result->failure);
    }

    public function testResolveFullChainWithValidChildZone(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [$childPrivateKey, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $childSignedData = self::buildDnskeySignedDataForZone(
            [$childDnskey],
            $childKeyTag,
            $expiration,
            $inception,
            'com',
        );
        $childSignature = null;
        openssl_sign($childSignedData, $childSignature, $childPrivateKey, OPENSSL_ALGO_SHA256);

        $childDnskeyRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            $childSignature,
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => [$childDnskey]],
            childRrsigs: ['com' => $childDnskeyRrsig],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertCount(1, $result->keys);
        static::assertSame($childDnskey, $result->keys[0]);
    }

    public function testResolveChildWithBadDsRrsigSignatureReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $now + 86_400,
            $now - 86_400,
            $rootKeyTag,
            '',
            'invalid-signature-data',
        );

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
            $dsRrsig,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::SignatureVerificationFailed, $result->failure);
    }

    public function testResolveChildWithInvalidDnskeyRrsigReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $childDnskeyRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            'invalid-child-sig',
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => [$childDnskey]],
            childRrsigs: ['com' => $childDnskeyRrsig],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::SignatureVerificationFailed, $result->failure);
    }

    public function testResolveChildZoneExceedingDnskeyLimitReturnsBogus(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $childDnskeys = [$childDnskey];
        for ($i = 0; $i < 8; $i++) {
            $childDnskeys[] = new DNSKEYRecord(
                'com',
                Duration::seconds(3600),
                256,
                3,
                Algorithm::RSASHA256,
                $childRfc3110Key,
            );
        }

        $childDnskeyRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            'dummy',
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => $childDnskeys],
            childRrsigs: ['com' => $childDnskeyRrsig],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ResourceExhaustion, $result->failure);
    }

    public function testResolveWithDotZoneReturnsCorrectResult(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('.');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertCount(1, $result->keys);
    }

    /** @mago-expect lint:no-empty-catch-clause */
    public function testResolveAppendsDotToZoneNameForDsQuery(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $queriedNames = [];
        $queriedTypes = [];

        $inner = new class($rootDnskey, $rootRrsig, $queriedNames, $queriedTypes) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            private array $names;
            private array $types;

            public function __construct(
                private readonly DNSKEYRecord $rootDnskey,
                private readonly RRSIGRecord $rootRrsig,
                array &$names,
                array &$types,
            ) {
                $this->names = &$names;
                $this->types = &$types;
            }

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                $this->names[] = $name;
                $this->types[] = $type;

                // @mago-expect lint:no-shorthand-ternary
                $normalizedName = rtrim($name, '.') ?: '.';
                if ($normalizedName === '.' && $type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, [$this->rootDnskey, $this->rootRrsig], [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        try {
            $resolver = new TrustChainResolver($inner, $anchor);
            $resolver->resolve('com');
        } catch (\Throwable) {
        }

        $dsQueryIdx = null;
        foreach ($queriedTypes as $i => $type) {
            if ($type !== RecordType::DS) {
                continue;
            }

            $dsQueryIdx = $i;
            break;
        }

        static::assertNotNull($dsQueryIdx);
        static::assertSame('com.', $queriedNames[$dsQueryIdx]);
    }

    /** @mago-expect lint:no-empty-catch-clause */
    public function testResolveDoesNotDoubleDotForFqdnZone(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $queriedNames = [];
        $queriedTypes = [];

        $inner = new class($rootDnskey, $rootRrsig, $queriedNames, $queriedTypes) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            private array $names;
            private array $types;

            public function __construct(
                private readonly DNSKEYRecord $rootDnskey,
                private readonly RRSIGRecord $rootRrsig,
                array &$names,
                array &$types,
            ) {
                $this->names = &$names;
                $this->types = &$types;
            }

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                $this->names[] = $name;
                $this->types[] = $type;

                // @mago-expect lint:no-shorthand-ternary
                $normalizedName = rtrim($name, '.') ?: '.';
                if ($normalizedName === '.' && $type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, [$this->rootDnskey, $this->rootRrsig], [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        try {
            $resolver = new TrustChainResolver($inner, $anchor);
            $resolver->resolve('com.');
        } catch (\Throwable) {
        }

        foreach ($queriedNames as $name) {
            static::assertStringNotContainsString('..', $name);
        }
    }

    /** @mago-expect lint:no-empty-catch-clause */
    public function testResolveDsQueryNameEndsWithDotForNonFqdn(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $queriedNames = [];
        $queriedTypes = [];

        $inner = new class($rootDnskey, $rootRrsig, $queriedNames, $queriedTypes) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            private array $names;
            private array $types;

            public function __construct(
                private readonly DNSKEYRecord $rootDnskey,
                private readonly RRSIGRecord $rootRrsig,
                array &$names,
                array &$types,
            ) {
                $this->names = &$names;
                $this->types = &$types;
            }

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                $this->names[] = $name;
                $this->types[] = $type;

                // @mago-expect lint:no-shorthand-ternary
                $normalizedName = rtrim($name, '.') ?: '.';
                if ($normalizedName === '.' && $type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, [$this->rootDnskey, $this->rootRrsig], [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        try {
            $resolver = new TrustChainResolver($inner, $anchor);
            $resolver->resolve('example.com');
        } catch (\Throwable) {
        }

        $dsNames = [];
        foreach ($queriedTypes as $i => $type) {
            if ($type !== RecordType::DS) {
                continue;
            }

            $dsNames[] = $queriedNames[$i];
        }

        static::assertNotEmpty($dsNames);
        foreach ($dsNames as $name) {
            static::assertStringEndsWith('.', $name);
        }
    }

    public function testResolveChildZoneReturnsBogusWhenParentKeysEmpty(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $now + 86_400,
            $now - 86_400,
            99_999,
            'unknown-parent.',
            'dummy-signature',
        );

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
            $dsRrsig,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertNotNull($result->failure);
        static::assertSame(ChainFailure::ChainBroken, $result->failure);
    }

    public function testResolveReturnsBogusNotNullWhenDsMismatch(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = new DSRecord(
            'com.',
            Duration::seconds(3600),
            $childKeyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            'deadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeef',
        );

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $childDnskeyRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $childKeyTag,
            'com',
            'dummy',
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => [$childDnskey]],
            childRrsigs: ['com' => $childDnskeyRrsig],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ChainBroken, $result->failure);
        static::assertSame([], $result->keys);
    }

    public function testResolveValidatedKeysAreFilteredToDnskeyType(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig);
        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('.');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        foreach ($result->keys as $key) {
            static::assertInstanceOf(DNSKEYRecord::class, $key);
        }
    }

    public function testResolveValidatedKeysAreReindexed(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig);
        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('.');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        $keys = $result->keys;
        $expectedIndex = 0;
        foreach ($keys as $index => $key) {
            static::assertSame($expectedIndex, $index);
            $expectedIndex++;
        }
    }

    public function testResolveParentZoneFromRrsigSignerNotEmpty(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsigWithDotSigner = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '.',
            $dsSignature,
        );

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
            $dsRrsigWithDotSigner,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertNotNull($result);
        static::assertSame(TrustChainStatus::Bogus, $result->status);
    }

    public function testResolveParentZoneWithEmptySignerUsesRoot(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $dsSignedData = self::buildDsSignedData([$dsRecord], $rootKeyTag, $expiration, $inception, '', 1, 3600);
        $dsSignature = null;
        openssl_sign($dsSignedData, $dsSignature, $rootPrivateKey, OPENSSL_ALGO_SHA256);

        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $expiration,
            $inception,
            $rootKeyTag,
            '',
            $dsSignature,
        );

        $inner = self::createFullChainResolver(
            rootDnskey: $rootDnskey,
            rootRrsig: $rootRrsig,
            dsAnswers: ['com' => [$dsRecord, $dsRrsig]],
            childDnskeys: ['com' => []],
        );

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertNotNull($result);
        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::MissingDnskey, $result->failure);
    }

    public function testResolveParentZoneWithNonRootNonEmptySigner(): void
    {
        [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig] = self::buildValidRoot();

        [, $childRfc3110Key] = self::generateRsaKeyPair();
        $childDnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $childRfc3110Key);
        $childKeyTag = KeyTag::compute($childDnskey);

        $dsRecord = self::buildDsRecord('com', $childDnskey, $childKeyTag);

        $now = Timestamp::now()->getSeconds();
        $dsRrsig = new RRSIGRecord(
            'com.',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            1,
            3600,
            $now + 86_400,
            $now - 86_400,
            $rootKeyTag,
            'nonexistent-parent.',
            'dummy',
        );

        $inner = self::createFullChainResolver(rootDnskey: $rootDnskey, rootRrsig: $rootRrsig, dsAnswers: ['com' => [
            $dsRecord,
            $dsRrsig,
        ]]);

        $resolver = new TrustChainResolver($inner, $anchor);
        $result = $resolver->resolve('com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::ChainBroken, $result->failure);
    }

    /**
     * @return array{OpenSSLAsymmetricKey, string, DNSKEYRecord, int, TrustAnchor, RRSIGRecord}
     */
    private static function buildValidRoot(): array
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
                '.',
                Duration::seconds(0),
                $rootKeyTag,
                Algorithm::RSASHA256,
                DigestAlgorithm::SHA256,
                $digestHex,
            ),
        ]);

        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;
        $rootSignedData = self::buildDnskeySignedDataForZone([$rootDnskey], $rootKeyTag, $expiration, $inception, '');
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

        return [$rootPrivateKey, $rootRfc3110Key, $rootDnskey, $rootKeyTag, $anchor, $rootRrsig];
    }

    private static function buildDsRecord(string $zone, DNSKEYRecord $dnskey, int $keyTag): DSRecord
    {
        $ownerWire = Encoder::encodeName($zone);
        $dnskeyRdata = new Writer()
            ->u16($dnskey->flags)
            ->u8($dnskey->protocol)
            ->u8($dnskey->algorithm->value)
            ->bytes($dnskey->publicKey)
            ->toString();
        $digestHex = hash('sha256', $ownerWire . $dnskeyRdata);

        return new DSRecord(
            $zone . '.',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            $digestHex,
        );
    }

    /**
     * @param array<string, list<RecordInterface>> $dsAnswers
     * @param array<string, list<DNSKEYRecord>> $childDnskeys
     * @param array<string, RRSIGRecord|null> $childRrsigs
     */
    private static function createFullChainResolver(
        DNSKEYRecord $rootDnskey,
        RRSIGRecord $rootRrsig,
        array $dsAnswers = [],
        array $childDnskeys = [],
        array $childRrsigs = [],
    ): ResolverInterface {
        return new class($rootDnskey, $rootRrsig, $dsAnswers, $childDnskeys, $childRrsigs) implements
            ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            /**
             * @param array<string, list<RecordInterface>> $dsAnswers
             * @param array<string, list<DNSKEYRecord>> $childDnskeys
             * @param array<string, RRSIGRecord|null> $childRrsigs
             */
            public function __construct(
                private readonly DNSKEYRecord $rootDnskey,
                private readonly RRSIGRecord $rootRrsig,
                private readonly array $dsAnswers,
                private readonly array $childDnskeys,
                private readonly array $childRrsigs,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                // @mago-expect lint:no-shorthand-ternary
                $normalizedName = rtrim($name, '.') ?: '.';

                if ($normalizedName === '.' && $type === RecordType::DNSKEY) {
                    return new Response(1, ResponseCode::NoError, [$this->rootDnskey, $this->rootRrsig], [], []);
                }

                if ($type === RecordType::DS) {
                    $dsData = $this->dsAnswers[$normalizedName] ?? [];
                    return new Response(1, ResponseCode::NoError, $dsData, [], []);
                }

                if ($type === RecordType::DNSKEY) {
                    $keys = $this->childDnskeys[$normalizedName] ?? [];
                    $rrsig = $this->childRrsigs[$normalizedName] ?? null;
                    $answers = [...$keys];
                    if ($rrsig !== null) {
                        $answers[] = $rrsig;
                    }

                    return new Response(1, ResponseCode::NoError, $answers, [], []);
                }

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };
    }

    /**
     * @param list<DNSKEYRecord> $dnskeys
     */
    private static function buildDnskeySignedDataForZone(
        array $dnskeys,
        int $keyTag,
        int $expiration,
        int $inception,
        string $signer,
    ): string {
        $signerWire = Encoder::encodeName($signer);
        $labels = $signer === ''
            ? 0
            : count(array_filter(explode('.', $signer), static fn(string $l): bool => $l !== ''));

        $rrsigPrefix = new Writer()
            ->u16(RecordType::DNSKEY->value)
            ->u8(8)
            ->u8($labels)
            ->u32(3600)
            ->u32($expiration)
            ->u32($inception)
            ->u16($keyTag)
            ->bytes($signerWire)
            ->toString();

        $canonicalRrs = [];
        $ownerName = $signer === '' ? '.' : $signer;
        foreach ($dnskeys as $dnskey) {
            $ownerWire = Encoder::encodeName(Byte\lowercase($ownerName));
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

        $canonicalRrs = Vec\sort($canonicalRrs);

        return $rrsigPrefix . Str\join($canonicalRrs, '');
    }

    /**
     * @param list<DSRecord> $dsRecords
     */
    private static function buildDsSignedData(
        array $dsRecords,
        int $keyTag,
        int $expiration,
        int $inception,
        string $signer,
        int $labels,
        int $originalTtl,
    ): string {
        $signerWire = Encoder::encodeName($signer);

        $rrsigPrefix = new Writer()
            ->u16(RecordType::DS->value)
            ->u8(8)
            ->u8($labels)
            ->u32($originalTtl)
            ->u32($expiration)
            ->u32($inception)
            ->u16($keyTag)
            ->bytes($signerWire)
            ->toString();

        $canonicalRrs = [];
        foreach ($dsRecords as $ds) {
            $ownerWire = Encoder::encodeName(Byte\lowercase($ds->name));
            $digestBytes = hex2bin($ds->digest);
            if ($digestBytes === false) {
                $digestBytes = '';
            }

            $rdata = new Writer()
                ->u16($ds->keyTag)
                ->u8($ds->algorithm->value)
                ->u8($ds->digestType->value)
                ->bytes($digestBytes)
                ->toString();

            $canonicalRrs[] = new Writer()
                ->bytes($ownerWire)
                ->u16(RecordType::DS->value)
                ->u16(1)
                ->u32($originalTtl)
                ->u16(Byte\length($rdata))
                ->bytes($rdata)
                ->toString();
        }

        $canonicalRrs = Vec\sort($canonicalRrs);

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
}
