<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\H2\ClientConnection;
use Psl\H2\ConnectionInterface;
use Psl\H2\ErrorCode;
use Psl\H2\Event;
use Psl\H2\Exception\RuntimeException;
use Psl\H2\Frame;
use Psl\H2\ServerConfiguration;
use Psl\H2\ServerConnection;
use Psl\H2\Setting;
use Psl\H2\StreamState;
use Psl\HPACK\Header;
use Psl\Network;

/**
 * End-to-end integration tests using socket pairs.
 *
 * Each test creates a client and server connection over a real socket pair,
 * performs the connection handshake, and exercises the full protocol flow.
 *
 * @mago-expect lint:kan-defect
 */
final class IntegrationTest extends TestCase
{
    /** @var list<Network\StreamInterface> */
    private array $sockets = [];

    protected function tearDown(): void
    {
        foreach ($this->sockets as $socket) {
            $socket->close();
        }

        $this->sockets = [];
    }

    public function testHandshake(): void
    {
        [$client, $server] = $this->createPair();

        $server->readClientPreface();
        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);
        static::assertSame([], $events[0]->settings);

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);
        static::assertSame([], $events[0]->settings);

        static::assertTrue($client->isConnected());
        static::assertTrue($server->isConnected());
    }

    public function testRequestResponse(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\HeadersReceived::class, $events[0]);
        static::assertSame($streamId, $events[0]->streamId);
        static::assertFalse($events[0]->endStream);

        $client->sendData($streamId, 'hello', true);

        $events = $server->readEvent();
        static::assertNotEmpty($events);
        static::assertInstanceOf(Event\DataReceived::class, $events[0]);
        static::assertSame('hello', $events[0]->data);
        static::assertTrue($events[0]->endStream);

        $server->sendHeadersWithStatus($streamId, '200', [
            new Header('content-type', 'text/plain'),
        ]);
        $server->sendData($streamId, 'world', true);

        $headersReceived = null;
        $dataReceived = null;
        while ($headersReceived === null || $dataReceived === null) {
            $events = $client->readEvent();
            foreach ($events as $event) {
                if ($event instanceof Event\HeadersReceived) {
                    $headersReceived = $event;
                } elseif ($event instanceof Event\DataReceived) {
                    $dataReceived = $event;
                }
            }
        }

        static::assertSame($streamId, $headersReceived->streamId);
        static::assertSame('world', $dataReceived->data);
        static::assertTrue($dataReceived->endStream);
    }

    public function testPingPong(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $client->ping('12345678');

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\PingReceived::class, $events[0]);
        static::assertSame('12345678', $events[0]->opaqueData);
        static::assertFalse($events[0]->ack);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\PingReceived::class, $events[0]);
        static::assertTrue($events[0]->ack);
        static::assertSame('12345678', $events[0]->opaqueData);
    }

    public function testResetStream(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders(
            $streamId,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        $server->readEvent();

        $server->resetStream($streamId, ErrorCode::Cancel);

        $events = $client->readEvent();
        static::assertCount(2, $events);
        static::assertInstanceOf(Event\StreamReset::class, $events[0]);
        static::assertSame($streamId, $events[0]->streamId);
        static::assertSame(ErrorCode::Cancel, $events[0]->errorCode);
        static::assertInstanceOf(Event\StreamClosed::class, $events[1]);
    }

    public function testGoAway(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->goAway(ErrorCode::NoError, 'shutting down');

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\GoAwayReceived::class, $events[0]);
        static::assertSame(ErrorCode::NoError, $events[0]->errorCode);
        static::assertSame('shutting down', $events[0]->debugData);

        static::assertFalse($client->isConnected());
    }

    public function testWindowUpdate(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'POST'),
            new Header(':path', '/upload'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $data = str_repeat('x', 1000);
        $client->sendData($streamId, $data);

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\DataReceived::class, $events[0]);
        static::assertSame($data, $events[0]->data);

        $events = $client->readEvent();
        $hasWindowUpdate = false;
        foreach ($events as $event) {
            if (!$event instanceof Event\WindowUpdated) {
                continue;
            }

            $hasWindowUpdate = true;
        }

        static::assertTrue($hasWindowUpdate);
    }

    public function testStreamState(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();

        static::assertSame(StreamState::Idle, $client->getStreamState($streamId));

        $client->sendHeaders(
            $streamId,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        static::assertSame(StreamState::HalfClosedLocal, $client->getStreamState($streamId));
        static::assertSame(1, $client->activeStreamCount());
    }

    public function testSendSettingsMidConnection(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendSettings([
            Setting::MaxConcurrentStreams->value => 50,
        ]);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);
        static::assertArrayHasKey(Setting::MaxConcurrentStreams->value, $events[0]->settings);
        static::assertSame(50, $events[0]->settings[Setting::MaxConcurrentStreams->value]);

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\SettingsReceived::class, $events[0]);
        static::assertSame([], $events[0]->settings);
    }

    public function testMultipleStreams(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $stream1 = $client->nextStreamId();
        $stream3 = $client->nextStreamId();

        $client->sendHeaders(
            $stream1,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/a'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        $client->sendHeaders(
            $stream3,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/b'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        static::assertSame(2, $client->activeStreamCount());

        $events1 = $server->readEvent();
        $events3 = $server->readEvent();

        static::assertInstanceOf(Event\HeadersReceived::class, $events1[0]);
        static::assertInstanceOf(Event\HeadersReceived::class, $events3[0]);

        $server->sendHeadersWithStatus($stream1, '200', [], true);
        $server->sendHeadersWithStatus($stream3, '404', [], true);

        $client->readEvent(); // stream 1 response
        $client->readEvent(); // stream 3 response
    }

    public function testBufferedWrites(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();

        $client->buffered(static function () use ($client, $streamId): void {
            $client->sendHeaders($streamId, [
                new Header(':method', 'POST'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ]);

            $client->sendData($streamId, 'buffered data', true);
        });

        $events = $server->readEvent();
        static::assertInstanceOf(Event\HeadersReceived::class, $events[0]);

        $events = $server->readEvent();
        static::assertInstanceOf(Event\DataReceived::class, $events[0]);
        static::assertSame('buffered data', $events[0]->data);
    }

    public function testSendPriority(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $client->sendPriority($streamId, 0, 128, false);

        $events = $server->readEvent();
        static::assertSame([], $events);
    }

    public function testSendPriorityUpdate(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $client->sendPriorityUpdate($streamId, 'u=0');

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\PriorityUpdateReceived::class, $events[0]);
        static::assertSame($streamId, $events[0]->streamId);
        static::assertSame('u=0', $events[0]->fieldValue);
    }

    public function testSendPriorityUpdateWithIncrementalFlag(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $client->sendPriorityUpdate($streamId, 'u=7, i');

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\PriorityUpdateReceived::class, $events[0]);
        static::assertSame('u=7, i', $events[0]->fieldValue);
    }

    public function testSendPriorityUpdateEmptyFieldValue(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $client->sendPriorityUpdate($streamId, '');

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\PriorityUpdateReceived::class, $events[0]);
        static::assertSame('', $events[0]->fieldValue);
    }

    public function testAltSvcOnStream0(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendAltSvc(0, 'https://example.com', 'h3=":443"; ma=2592000');

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\AltSvcReceived::class, $events[0]);
        static::assertSame(0, $events[0]->streamId);
        static::assertSame('https://example.com', $events[0]->origin);
        static::assertSame('h3=":443"; ma=2592000', $events[0]->fieldValue);
    }

    public function testAltSvcOnStream(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders(
            $streamId,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        $server->readEvent();

        $server->sendAltSvc($streamId, '', 'h3=":8443"');

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\AltSvcReceived::class, $events[0]);
        static::assertSame($streamId, $events[0]->streamId);
        static::assertSame('', $events[0]->origin);
        static::assertSame('h3=":8443"', $events[0]->fieldValue);
    }

    public function testAltSvcClearWithEmptyFieldValue(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendAltSvc(0, 'https://example.com', 'clear');

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\AltSvcReceived::class, $events[0]);
        static::assertSame('clear', $events[0]->fieldValue);
    }

    public function testOriginFrame(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendOrigin([
            'https://example.com',
            'https://cdn.example.com',
            'https://api.example.com:8443',
        ]);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\OriginReceived::class, $events[0]);
        static::assertSame(
            [
                'https://example.com',
                'https://cdn.example.com',
                'https://api.example.com:8443',
            ],
            $events[0]->origins,
        );
    }

    public function testOriginFrameSingleOrigin(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendOrigin(['https://example.com']);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\OriginReceived::class, $events[0]);
        static::assertSame(['https://example.com'], $events[0]->origins);
    }

    public function testOriginFrameEmptyList(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->sendOrigin([]);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\OriginReceived::class, $events[0]);
        static::assertSame([], $events[0]->origins);
    }

    public function testExtendedConnect(): void
    {
        [$client, $server] = $this->createPair(serverSettings: [Setting::EnableConnectProtocol->value => 1]);
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendExtendedConnect($streamId, 'websocket', 'https', 'example.com', '/chat');

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\HeadersReceived::class, $events[0]);
        static::assertSame($streamId, $events[0]->streamId);
        static::assertFalse($events[0]->endStream);

        $headerMap = [];
        foreach ($events[0]->headers as $header) {
            $headerMap[$header->name] = $header->value;
        }

        static::assertSame('CONNECT', $headerMap[':method']);
        static::assertSame('websocket', $headerMap[':protocol']);
        static::assertSame('https', $headerMap[':scheme']);
        static::assertSame('example.com', $headerMap[':authority']);
        static::assertSame('/chat', $headerMap[':path']);

        $server->sendHeadersWithStatus($streamId, '200', []);

        $events = $client->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\HeadersReceived::class, $events[0]);

        $client->sendData($streamId, 'client hello');

        $serverData = $this->readUntilEvent($server, Event\DataReceived::class);
        static::assertSame('client hello', $serverData->data);

        $server->sendData($streamId, 'server hello');

        $clientData = $this->readUntilEvent($client, Event\DataReceived::class);
        static::assertSame('server hello', $clientData->data);
    }

    public function testExtendedConnectWithExtraHeaders(): void
    {
        [$client, $server] = $this->createPair(serverSettings: [Setting::EnableConnectProtocol->value => 1]);
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendExtendedConnect($streamId, 'websocket', 'https', 'example.com', '/chat', [
            new Header('origin', 'https://example.com'),
            new Header('sec-websocket-version', '13'),
        ]);

        $events = $server->readEvent();
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\HeadersReceived::class, $events[0]);

        $headerMap = [];
        foreach ($events[0]->headers as $header) {
            $headerMap[$header->name] = $header->value;
        }

        static::assertSame('websocket', $headerMap[':protocol']);
        static::assertSame('https://example.com', $headerMap['origin']);
        static::assertSame('13', $headerMap['sec-websocket-version']);
    }

    /**
     * @param array<positive-int, non-negative-int> $serverSettings
     *
     * @return array{ClientConnection, ServerConnection}
     */
    private function createPair(array $serverSettings = []): array
    {
        [$a, $b] = Network\socket_pair();
        $this->sockets[] = $a;
        $this->sockets[] = $b;

        $client = new ClientConnection($a);
        $server = new ServerConnection($b, new ServerConfiguration($serverSettings));

        $client->initialize();
        $server->initialize();

        return [$client, $server];
    }

    private function completeHandshake(ClientConnection $client, ServerConnection $server): void
    {
        $server->readClientPreface();
        $server->readEvent(); // client SETTINGS
        $client->readEvent(); // server SETTINGS
        $client->readEvent(); // SETTINGS ACK
        $server->readEvent(); // SETTINGS ACK
    }

    public function testWriteAllHandlesFlowControl(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'POST'),
            new Header(':path', '/upload'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent(); // headers

        $payload = str_repeat('x', 1000);
        $client->sendAllData($streamId, $payload, true);

        $received = '';
        $endStream = false;
        while (!$endStream) {
            $events = $server->readEvent();
            foreach ($events as $event) {
                if (!$event instanceof Event\DataReceived) {
                    continue;
                }

                $received .= $event->data;
                $endStream = $event->endStream;
            }
        }

        static::assertSame($payload, $received);
    }

    public function testWriteAllEmptyWithEndStream(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders($streamId, [
            new Header(':method', 'POST'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $server->readEvent();

        $client->sendAllData($streamId, '', true);

        $data = $this->readUntilEvent($server, Event\DataReceived::class);
        static::assertSame('', $data->data);
        static::assertTrue($data->endStream);
    }

    public function testRejectPush(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders(
            $streamId,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        $server->readEvent();

        $promisedStreamId = $server->nextStreamId();
        $server->sendPushPromise($streamId, $promisedStreamId, [
            new Header(':method', 'GET'),
            new Header(':path', '/style.css'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);

        $push = $this->readUntilEvent($client, Event\PushPromiseReceived::class);
        static::assertSame($promisedStreamId, $push->promisedStreamId);

        $client->rejectPush($promisedStreamId);

        $reset = $this->readUntilEvent($server, Event\StreamReset::class);
        static::assertSame($promisedStreamId, $reset->streamId);
        static::assertSame(ErrorCode::Cancel, $reset->errorCode);
    }

    public function testAutoGoAwayOnProtocolError(): void
    {
        [$clientSocket, $serverSocket] = Network\socket_pair();
        $this->sockets[] = $clientSocket;
        $this->sockets[] = $serverSocket;

        $client = new ClientConnection($clientSocket);
        $server = new ServerConnection($serverSocket);

        $client->initialize();
        $server->initialize();
        $this->completeHandshake($client, $server);

        $streamId = $client->nextStreamId();
        $client->sendHeaders(
            $streamId,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        $server->readEvent(); // headers

        $malformed = new Frame\RawFrame(0x3, 0, $streamId, "\x00\x00");
        $clientSocket->writeAll(Frame\encode($malformed));

        // Server reads the malformed frame - auto-sends GOAWAY then throws
        $serverThrew = false;
        try {
            $server->readEvent();
        } catch (RuntimeException) {
            $serverThrew = true;
        }

        static::assertTrue($serverThrew);

        // GOAWAY was written to the socket by auto-GOAWAY.
        // Client reads it sequentially (data is already in the socket buffer).
        $goaway = null;
        $events = $client->readEvent();
        foreach ($events as $event) {
            if (!$event instanceof Event\GoAwayReceived) {
                continue;
            }

            $goaway = $event;
        }

        static::assertNotNull($goaway);
    }

    /**
     * Read events until one of the expected type is found.
     *
     * @template T of Event\EventInterface
     *
     * @param class-string<T> $eventClass
     *
     * @return T
     */
    private function readUntilEvent(ConnectionInterface $conn, string $eventClass): Event\EventInterface
    {
        while (true) {
            $events = $conn->readEvent();
            foreach ($events as $event) {
                if ($event instanceof $eventClass) {
                    return $event;
                }
            }
        }
    }

    public function testMultipleStreamsWithInterleavedData(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $s1 = $client->nextStreamId();
        $s3 = $client->nextStreamId();

        $client->sendHeaders($s1, [
            new Header(':method', 'POST'),
            new Header(':path', '/a'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);
        $client->sendHeaders($s3, [
            new Header(':method', 'POST'),
            new Header(':path', '/b'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);
        $server->readEvent();
        $server->readEvent();

        $client->sendData($s1, 'A1');
        $client->sendData($s3, 'B1');
        $client->sendData($s1, 'A2', true);
        $client->sendData($s3, 'B2', true);

        $data = []; // streamId => string
        for ($i = 0; $i < 4; $i++) {
            foreach ($server->readEvent() as $e) {
                if (!$e instanceof Event\DataReceived) {
                    continue;
                }

                $data[$e->streamId] = ($data[$e->streamId] ?? '') . $e->data;
            }
        }

        static::assertSame('A1A2', $data[$s1]);
        static::assertSame('B1B2', $data[$s3]);
    }

    public function testStreamStateTransitions(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $sid = $client->nextStreamId();
        static::assertSame(StreamState::Idle, $client->getStreamState($sid));

        $client->sendHeaders($sid, [
            new Header(':method', 'POST'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);
        static::assertSame(StreamState::Open, $client->getStreamState($sid));
        static::assertSame(1, $client->activeStreamCount());

        $client->sendData($sid, 'done', true);
        static::assertSame(StreamState::HalfClosedLocal, $client->getStreamState($sid));

        $server->readEvent();
        $server->readEvent();
        $server->sendHeadersWithStatus($sid, '200', [], true);

        // Read response - HeadersReceived and StreamClosed may come in one readEvent call
        $this->readUntilEvent($client, Event\HeadersReceived::class);

        // After receiving response with endStream on a HalfClosedLocal stream, it closes
        static::assertSame(StreamState::Closed, $client->getStreamState($sid));
        static::assertSame(0, $client->activeStreamCount());
    }

    public function testTrailers(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $sid = $client->nextStreamId();
        $client->sendHeaders($sid, [
            new Header(':method', 'POST'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);
        $client->sendData($sid, 'body');
        $client->sendHeaders($sid, [new Header('x-checksum', 'abc123')], true);

        $server->readEvent(); // headers
        $data = $this->readUntilEvent($server, Event\DataReceived::class);
        static::assertSame('body', $data->data);
        static::assertFalse($data->endStream);

        $trailers = $this->readUntilEvent($server, Event\HeadersReceived::class);
        static::assertTrue($trailers->endStream);

        $map = [];
        foreach ($trailers->headers as $h) {
            $map[$h->name] = $h->value;
        }

        static::assertSame('abc123', $map['x-checksum']);
    }

    public function testResetStreamWhileOpen(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $sid = $client->nextStreamId();
        $client->sendHeaders($sid, [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
            new Header(':scheme', 'https'),
            new Header(':authority', 'example.com'),
        ]);
        $server->readEvent();

        $client->resetStream($sid, ErrorCode::Cancel);
        $reset = $this->readUntilEvent($server, Event\StreamReset::class);
        static::assertSame($sid, $reset->streamId);
        static::assertSame(ErrorCode::Cancel, $reset->errorCode);
    }

    public function testServerResetBeforeResponse(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $sid = $client->nextStreamId();
        $client->sendHeaders(
            $sid,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );
        $server->readEvent();

        $server->resetStream($sid, ErrorCode::RefusedStream);
        $reset = $this->readUntilEvent($client, Event\StreamReset::class);
        static::assertSame(ErrorCode::RefusedStream, $reset->errorCode);
    }

    public function testBufferedMultipleStreams(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $s1 = $client->nextStreamId();
        $s3 = $client->nextStreamId();

        $client->buffered(static function () use ($client, $s1, $s3): void {
            $client->sendHeaders(
                $s1,
                [
                    new Header(':method', 'GET'),
                    new Header(':path', '/a'),
                    new Header(':scheme', 'https'),
                    new Header(':authority', 'example.com'),
                ],
                true,
            );
            $client->sendHeaders(
                $s3,
                [
                    new Header(':method', 'GET'),
                    new Header(':path', '/b'),
                    new Header(':scheme', 'https'),
                    new Header(':authority', 'example.com'),
                ],
                true,
            );
        });

        $h1 = $this->readUntilEvent($server, Event\HeadersReceived::class);
        static::assertSame($s1, $h1->streamId);
        $h3 = $this->readUntilEvent($server, Event\HeadersReceived::class);
        static::assertSame($s3, $h3->streamId);
    }

    public function testPingRoundTrip(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $client->ping('AAAAAAAA');

        $ping = $this->readUntilEvent($server, Event\PingReceived::class);
        static::assertSame('AAAAAAAA', $ping->opaqueData);
        static::assertFalse($ping->ack);

        $ack = $this->readUntilEvent($client, Event\PingReceived::class);
        static::assertTrue($ack->ack);
        static::assertSame('AAAAAAAA', $ack->opaqueData);
    }

    public function testIsConnectedAfterGoAway(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        static::assertTrue($client->isConnected());
        static::assertTrue($server->isConnected());

        $client->goAway(ErrorCode::NoError, 'bye');
        $this->readUntilEvent($server, Event\GoAwayReceived::class);

        static::assertFalse($server->isConnected());
        static::assertFalse($client->isConnected());
    }

    public function testActiveStreamCountDecreases(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $s1 = $client->nextStreamId();
        $s3 = $client->nextStreamId();

        $client->sendHeaders(
            $s1,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/1'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );
        $client->sendHeaders(
            $s3,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/2'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );

        static::assertSame(2, $client->activeStreamCount());

        $server->readEvent();
        $server->readEvent();
        $server->sendHeadersWithStatus($s1, '200', [], true);

        $this->readUntilEvent($client, Event\HeadersReceived::class);
        static::assertSame(1, $client->activeStreamCount());
    }

    public function testLastPeerStreamId(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        static::assertSame(0, $server->lastPeerStreamId());

        $s1 = $client->nextStreamId();
        $client->sendHeaders(
            $s1,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );
        $server->readEvent();
        static::assertSame($s1, $server->lastPeerStreamId());

        $s3 = $client->nextStreamId();
        $client->sendHeaders(
            $s3,
            [
                new Header(':method', 'GET'),
                new Header(':path', '/2'),
                new Header(':scheme', 'https'),
                new Header(':authority', 'example.com'),
            ],
            true,
        );
        $server->readEvent();
        static::assertSame($s3, $server->lastPeerStreamId());
    }

    public function testGoAwayPreventsNewStreams(): void
    {
        [$client, $server] = $this->createPair();
        $this->completeHandshake($client, $server);

        $server->goAway(ErrorCode::NoError);
        $this->readUntilEvent($client, Event\GoAwayReceived::class);
        static::assertFalse($client->isConnected());
    }
}
