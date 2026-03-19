<?php

declare(strict_types=1);

namespace Psl\H2\Event;

/**
 * Marker interface for all HTTP/2 connection and stream events.
 *
 * Every event emitted by the HTTP/2 connection implements this interface, making it
 * the single type that event handlers and listeners should accept. Implementations
 * are final readonly classes, each corresponding to a specific HTTP/2 frame type or
 * state transition defined in RFC 9113.
 *
 * Consumers should use instanceof checks or a match expression to dispatch on the
 * concrete event type.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9113 RFC 9113 - HTTP/2
 *
 * @psalm-inheritors DataReceived|GoAwayReceived|HeadersReceived|PingReceived|PushPromiseReceived|SettingsReceived|StreamClosed|StreamReset|WindowUpdated|PriorityUpdateReceived|AltSvcReceived|OriginReceived
 */
interface EventInterface {}
