<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Message\Exchange;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;

use function Psl\URL\parse;

final class TransactionTest extends TestCase
{
    public function testDefaultPushedIsNull(): void
    {
        $transaction = new Transaction([], null, new Response(status: 200, headers: FieldMap::default()));

        static::assertNull($transaction->pushed);
    }

    public function testCustomPushedExchanges(): void
    {
        $exchange = new Exchange(
            new Request(method: 'GET', url: parse('http://example.com/')),
            new Response(status: 200, headers: FieldMap::default()),
        );

        $awaitable = Async\Awaitable::complete([$exchange]);
        $transaction = new Transaction([], $awaitable, new Response(status: 200, headers: FieldMap::default()));

        $exchanges = $transaction->pushed->await();

        static::assertCount(1, $exchanges);
        static::assertSame($exchange, $exchanges[0]);
    }
}
