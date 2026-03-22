<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\DateTime;
use Psl\DateTime\Duration;
use Psl\SMTP\Client\SendConfiguration;
use Psl\SMTP\DeliverBy;
use Psl\SMTP\DeliverByMode;
use Psl\SMTP\Priority;

final class SendConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new SendConfiguration();

        static::assertNull($config->dsnReturn);
        static::assertNull($config->dsnEnvelopeId);
        static::assertNull($config->dsnNotify);
        static::assertFalse($config->requireTls);
        static::assertNull($config->priority);
        static::assertNull($config->deliverBy);
        static::assertNull($config->futureRelease);
    }

    public function testConstructorWithAllParameters(): void
    {
        $deliverBy = new DeliverBy(Duration::hours(1));
        $duration = Duration::minutes(30);

        $config = new SendConfiguration(
            dsnReturn: 'FULL',
            dsnEnvelopeId: 'env-001',
            dsnNotify: 'SUCCESS,FAILURE',
            requireTls: true,
            priority: Priority::Urgent,
            deliverBy: $deliverBy,
            futureRelease: $duration,
        );

        static::assertSame('FULL', $config->dsnReturn);
        static::assertSame('env-001', $config->dsnEnvelopeId);
        static::assertSame('SUCCESS,FAILURE', $config->dsnNotify);
        static::assertTrue($config->requireTls);
        static::assertSame(Priority::Urgent, $config->priority);
        static::assertSame($deliverBy, $config->deliverBy);
        static::assertSame($duration, $config->futureRelease);
    }

    public function testWithDsnReturn(): void
    {
        $config = new SendConfiguration();
        $new = $config->withDsnReturn('FULL');

        static::assertNull($config->dsnReturn);
        static::assertSame('FULL', $new->dsnReturn);
    }

    public function testWithDsnReturnNull(): void
    {
        $config = new SendConfiguration(dsnReturn: 'HDRS');
        $new = $config->withDsnReturn(null);

        static::assertSame('HDRS', $config->dsnReturn);
        static::assertNull($new->dsnReturn);
    }

    public function testWithDsnReturnPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(dsnEnvelopeId: 'env-001', requireTls: true, priority: Priority::Flash);

        $new = $config->withDsnReturn('FULL');

        static::assertSame('env-001', $new->dsnEnvelopeId);
        static::assertTrue($new->requireTls);
        static::assertSame(Priority::Flash, $new->priority);
    }

    public function testWithDsnEnvelopeId(): void
    {
        $config = new SendConfiguration();
        $new = $config->withDsnEnvelopeId('msg-123');

        static::assertNull($config->dsnEnvelopeId);
        static::assertSame('msg-123', $new->dsnEnvelopeId);
    }

    public function testWithDsnEnvelopeIdNull(): void
    {
        $config = new SendConfiguration(dsnEnvelopeId: 'old');
        $new = $config->withDsnEnvelopeId(null);

        static::assertSame('old', $config->dsnEnvelopeId);
        static::assertNull($new->dsnEnvelopeId);
    }

    public function testWithDsnEnvelopeIdPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(dsnReturn: 'FULL', dsnNotify: 'NEVER');

        $new = $config->withDsnEnvelopeId('env-002');

        static::assertSame('FULL', $new->dsnReturn);
        static::assertSame('NEVER', $new->dsnNotify);
    }

    public function testWithDsnNotify(): void
    {
        $config = new SendConfiguration();
        $new = $config->withDsnNotify('SUCCESS');

        static::assertNull($config->dsnNotify);
        static::assertSame('SUCCESS', $new->dsnNotify);
    }

    public function testWithDsnNotifyNull(): void
    {
        $config = new SendConfiguration(dsnNotify: 'FAILURE');
        $new = $config->withDsnNotify(null);

        static::assertSame('FAILURE', $config->dsnNotify);
        static::assertNull($new->dsnNotify);
    }

    public function testWithDsnNotifyPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(dsnReturn: 'HDRS', dsnEnvelopeId: 'x');

        $new = $config->withDsnNotify('DELAY');

        static::assertSame('HDRS', $new->dsnReturn);
        static::assertSame('x', $new->dsnEnvelopeId);
    }

    public function testWithRequireTls(): void
    {
        $config = new SendConfiguration();
        $new = $config->withRequireTls(true);

        static::assertFalse($config->requireTls);
        static::assertTrue($new->requireTls);
    }

    public function testWithRequireTlsFalse(): void
    {
        $config = new SendConfiguration(requireTls: true);
        $new = $config->withRequireTls(false);

        static::assertTrue($config->requireTls);
        static::assertFalse($new->requireTls);
    }

    public function testWithRequireTlsPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(dsnReturn: 'FULL', priority: Priority::Normal);

        $new = $config->withRequireTls(true);

        static::assertSame('FULL', $new->dsnReturn);
        static::assertSame(Priority::Normal, $new->priority);
    }

    public function testWithPriority(): void
    {
        $config = new SendConfiguration();
        $new = $config->withPriority(Priority::Urgent);

        static::assertNull($config->priority);
        static::assertSame(Priority::Urgent, $new->priority);
    }

    public function testWithPriorityNull(): void
    {
        $config = new SendConfiguration(priority: Priority::Flash);
        $new = $config->withPriority(null);

        static::assertSame(Priority::Flash, $config->priority);
        static::assertNull($new->priority);
    }

    public function testWithPriorityPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(requireTls: true, dsnNotify: 'SUCCESS');

        $new = $config->withPriority(Priority::Deferred);

        static::assertTrue($new->requireTls);
        static::assertSame('SUCCESS', $new->dsnNotify);
    }

    public function testWithAllPriorityValues(): void
    {
        $config = new SendConfiguration();

        foreach (Priority::cases() as $priority) {
            $new = $config->withPriority($priority);
            static::assertSame($priority, $new->priority);
        }
    }

    public function testWithDeliverBy(): void
    {
        $deliverBy = new DeliverBy(Duration::hours(2), DeliverByMode::Return);
        $config = new SendConfiguration();
        $new = $config->withDeliverBy($deliverBy);

        static::assertNull($config->deliverBy);
        static::assertSame($deliverBy, $new->deliverBy);
    }

    public function testWithDeliverByNull(): void
    {
        $deliverBy = new DeliverBy(Duration::minutes(30));
        $config = new SendConfiguration(deliverBy: $deliverBy);
        $new = $config->withDeliverBy(null);

        static::assertSame($deliverBy, $config->deliverBy);
        static::assertNull($new->deliverBy);
    }

    public function testWithDeliverByPreservesOtherProperties(): void
    {
        $config = new SendConfiguration(priority: Priority::Flash, requireTls: true);
        $deliverBy = new DeliverBy(Duration::hours(1), DeliverByMode::Notify);

        $new = $config->withDeliverBy($deliverBy);

        static::assertSame(Priority::Flash, $new->priority);
        static::assertTrue($new->requireTls);
    }

    public function testWithFutureReleaseDuration(): void
    {
        $duration = Duration::hours(6);
        $config = new SendConfiguration();
        $new = $config->withFutureRelease($duration);

        static::assertNull($config->futureRelease);
        static::assertSame($duration, $new->futureRelease);
        static::assertInstanceOf(Duration::class, $new->futureRelease);
    }

    public function testWithFutureReleaseDateTime(): void
    {
        $dateTime = DateTime::now();
        $config = new SendConfiguration();
        $new = $config->withFutureRelease($dateTime);

        static::assertNull($config->futureRelease);
        static::assertSame($dateTime, $new->futureRelease);
    }

    public function testWithFutureReleaseNull(): void
    {
        $config = new SendConfiguration(futureRelease: Duration::minutes(10));
        $new = $config->withFutureRelease(null);

        static::assertNotNull($config->futureRelease);
        static::assertNull($new->futureRelease);
    }

    public function testWithFutureReleasePreservesOtherProperties(): void
    {
        $deliverBy = new DeliverBy(Duration::hours(1));
        $config = new SendConfiguration(dsnReturn: 'FULL', priority: Priority::NonUrgent, deliverBy: $deliverBy);

        $new = $config->withFutureRelease(Duration::hours(12));

        static::assertSame('FULL', $new->dsnReturn);
        static::assertSame(Priority::NonUrgent, $new->priority);
        static::assertSame($deliverBy, $new->deliverBy);
    }

    public function testImmutability(): void
    {
        $config = new SendConfiguration();

        $a = $config->withDsnReturn('FULL');
        $b = $config->withDsnEnvelopeId('env');
        $c = $config->withRequireTls(true);

        static::assertNull($config->dsnReturn);
        static::assertNull($config->dsnEnvelopeId);
        static::assertFalse($config->requireTls);

        static::assertNotSame($config, $a);
        static::assertNotSame($config, $b);
        static::assertNotSame($config, $c);
        static::assertNotSame($a, $b);
    }

    public function testChainedWithers(): void
    {
        $config = new SendConfiguration()
            ->withDsnReturn('FULL')
            ->withDsnEnvelopeId('chain-test')
            ->withDsnNotify('SUCCESS,FAILURE')
            ->withRequireTls(true)
            ->withPriority(Priority::Urgent);

        static::assertSame('FULL', $config->dsnReturn);
        static::assertSame('chain-test', $config->dsnEnvelopeId);
        static::assertSame('SUCCESS,FAILURE', $config->dsnNotify);
        static::assertTrue($config->requireTls);
        static::assertSame(Priority::Urgent, $config->priority);
    }
}
