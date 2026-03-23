<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResponseCode;
use Psl\DNS\StaticResolver;
use Psl\IP\Address;

final class StaticResolverResponseCodeTest extends TestCase
{
    public function testUnknownNameReturnsNxdomain(): void
    {
        $resolver = new StaticResolver([]);

        $response = $resolver->query('nonexistent.example.com', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
        static::assertSame([], $response->answers);
    }

    public function testKnownNameReturnsNoError(): void
    {
        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'))],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
    }

    public function testKnownNameUnknownKindReturnsNoError(): void
    {
        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'))],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([], $response->answers);
    }
}
