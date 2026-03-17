<?php

declare(strict_types=1);

namespace Psl\Channel\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Channel;

#[Groups(['channel'])]
final class ChannelBench
{
    /**
     * @param array{messages: int, capacity: positive-int} $params
     */
    #[ParamProviders('provideBoundedData')]
    public function benchBoundedChannelSendReceive(array $params): void
    {
        [$receiver, $sender] = Channel\bounded($params['capacity']);
        for ($i = 0; $i < $params['messages']; $i++) {
            $sender->send($i);
            $receiver->receive();
        }
    }

    /**
     * @param array{messages: int} $params
     */
    #[ParamProviders('provideUnboundedData')]
    public function benchUnboundedChannelSendReceive(array $params): void
    {
        [$receiver, $sender] = Channel\unbounded();
        for ($i = 0; $i < $params['messages']; $i++) {
            $sender->send($i);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $receiver->receive();
        }
    }

    /**
     * @param array{messages: int} $params
     */
    #[ParamProviders('provideUnboundedData')]
    public function benchUnboundedChannelInterleaved(array $params): void
    {
        [$receiver, $sender] = Channel\unbounded();
        for ($i = 0; $i < $params['messages']; $i++) {
            $sender->send($i);
            $receiver->receive();
        }
    }

    /**
     * @return iterable<string, array{messages: int, capacity: int}>
     */
    public function provideBoundedData(): iterable
    {
        yield 'small' => ['messages' => 100, 'capacity' => 10];
        yield 'medium' => ['messages' => 1000, 'capacity' => 50];
        yield 'large' => ['messages' => 10_000, 'capacity' => 100];
    }

    /**
     * @return iterable<string, array{messages: int}>
     */
    public function provideUnboundedData(): iterable
    {
        yield 'small' => ['messages' => 100];
        yield 'medium' => ['messages' => 1000];
        yield 'large' => ['messages' => 10_000];
    }
}
