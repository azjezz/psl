<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNS\StaticResolver;
use Psl\DNSSEC\Exception\SignatureFailedException;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\DNSSEC\SecureResolver;
use Psl\DNSSEC\TrustChainResolverInterface;
use Psl\DNSSEC\TrustChainResult;
use Psl\DNSSEC\TrustChainStatus;
use Psl\IP\Address;

final class SecureResolverDosTest extends TestCase
{
    public function testMalformedDnskeyDoesNotCrashResolver(): void
    {
        $malformedKey = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            257,
            3,
            Algorithm::RSASHA256,
            "\x01\x02",
        );
        $keyTag = KeyTag::compute($malformedKey);

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
            'dummy-signature',
        );

        $aRecord = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));

        $inner = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [$aRecord, $rrsig],
            ],
        ]);

        $trustChain = new readonly class([$malformedKey]) implements TrustChainResolverInterface {
            /**
             * @param list<DNSKEYRecord> $keys
             */
            public function __construct(
                private array $keys,
            ) {}

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Secure, $this->keys);
            }
        };

        $resolver = new SecureResolver($inner, $trustChain);

        try {
            $resolver->query('example.com', RecordType::A);
            static::fail('Expected SignatureFailedException but query succeeded.');
        } catch (SignatureFailedException) {
            static::addToAssertionCount(1);
        }
    }
}
