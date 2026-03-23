<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\MXRecord;
use Psl\DNS\Record\NSRecord;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\IP\Address;

final class ResponseTest extends TestCase
{
    public function testConstruction(): void
    {
        $answer = new ARecord('example.com', Duration::seconds(300), Address::v4('93.184.216.34'));
        $authority = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');

        $response = new Response(
            id: 12_345,
            code: ResponseCode::NoError,
            answers: [$answer],
            authority: [$authority],
            additional: [],
        );

        static::assertSame(12_345, $response->id);
        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertCount(1, $response->answers);
        static::assertSame($answer, $response->answers[0]);
        static::assertCount(1, $response->authority);
        static::assertSame($authority, $response->authority[0]);
        static::assertCount(0, $response->additional);
    }

    public function testEmptyResponse(): void
    {
        $response = new Response(
            id: 1,
            code: ResponseCode::NonExistentDomain,
            answers: [],
            authority: [],
            additional: [],
        );

        static::assertSame(1, $response->id);
        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
        static::assertCount(0, $response->answers);
    }

    public function testGetAnswerRecords(): void
    {
        $a1 = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $a2 = new ARecord('example.com', Duration::seconds(300), Address::v4('5.6.7.8'));
        $ns = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');

        $response = new Response(1, ResponseCode::NoError, [$a1, $ns, $a2], [], []);

        $aRecords = $response->getAnswerRecords(ARecord::class);

        static::assertCount(2, $aRecords);
        static::assertSame($a1, $aRecords[0]);
        static::assertSame($a2, $aRecords[1]);
    }

    public function testGetAnswerRecordsReturnsEmptyWhenNoMatch(): void
    {
        $a = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));

        $response = new Response(1, ResponseCode::NoError, [$a], [], []);

        static::assertSame([], $response->getAnswerRecords(MXRecord::class));
    }

    public function testGetFirstAnswerRecord(): void
    {
        $a1 = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $a2 = new ARecord('example.com', Duration::seconds(300), Address::v4('5.6.7.8'));

        $response = new Response(1, ResponseCode::NoError, [$a1, $a2], [], []);

        static::assertSame($a1, $response->getFirstAnswerRecord(ARecord::class));
    }

    public function testGetFirstAnswerRecordReturnsNullWhenNoMatch(): void
    {
        $a = new ARecord('example.com', Duration::seconds(300), Address::v4('1.2.3.4'));

        $response = new Response(1, ResponseCode::NoError, [$a], [], []);

        static::assertNull($response->getFirstAnswerRecord(MXRecord::class));
    }

    public function testGetAuthorityRecords(): void
    {
        $ns1 = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');
        $ns2 = new NSRecord('example.com', Duration::seconds(172_800), 'ns2.example.com');

        $response = new Response(1, ResponseCode::NoError, [], [$ns1, $ns2], []);

        $nsRecords = $response->getAuthorityRecords(NSRecord::class);

        static::assertCount(2, $nsRecords);
        static::assertSame($ns1, $nsRecords[0]);
        static::assertSame($ns2, $nsRecords[1]);
    }

    public function testGetAuthorityRecordsFiltersMixedTypes(): void
    {
        $ns = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');
        $a = new ARecord('ns1.example.com', Duration::seconds(300), Address::v4('1.2.3.4'));

        $response = new Response(1, ResponseCode::NoError, [], [$ns, $a], []);

        $nsRecords = $response->getAuthorityRecords(NSRecord::class);
        static::assertCount(1, $nsRecords);
        static::assertSame($ns, $nsRecords[0]);

        $aRecords = $response->getAuthorityRecords(ARecord::class);
        static::assertCount(1, $aRecords);
        static::assertSame($a, $aRecords[0]);
    }

    public function testGetAdditionalRecords(): void
    {
        $a = new ARecord('ns1.example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $aaaa = new AAAARecord('ns1.example.com', Duration::seconds(300), Address::v6('2001:db8::1'));

        $response = new Response(1, ResponseCode::NoError, [], [], [$a, $aaaa]);

        $aRecords = $response->getAdditionalRecords(ARecord::class);

        static::assertCount(1, $aRecords);
        static::assertSame($a, $aRecords[0]);
    }

    public function testGetAdditionalRecordsFiltersMixedTypes(): void
    {
        $a = new ARecord('ns1.example.com', Duration::seconds(300), Address::v4('1.2.3.4'));
        $ns = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');
        $aaaa = new AAAARecord('ns1.example.com', Duration::seconds(300), Address::v6('2001:db8::1'));

        $response = new Response(1, ResponseCode::NoError, [], [], [$a, $ns, $aaaa]);

        $aRecords = $response->getAdditionalRecords(ARecord::class);
        static::assertCount(1, $aRecords);
        static::assertSame($a, $aRecords[0]);

        $aaaaRecords = $response->getAdditionalRecords(AAAARecord::class);
        static::assertCount(1, $aaaaRecords);
        static::assertSame($aaaa, $aaaaRecords[0]);
    }
}
