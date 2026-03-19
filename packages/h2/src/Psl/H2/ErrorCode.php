<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\H2\Frame\FrameType;

/**
 * HTTP/2 error codes as defined in RFC 9113 Section 7.
 *
 * Error codes are 32-bit fields used in RST_STREAM ({@see FrameType::RstStream}) and
 * GOAWAY ({@see FrameType::GoAway}) frames to convey the reason for the stream or
 * connection error. Unknown or unsupported error codes MUST NOT trigger any special
 * behavior; they MAY be treated as equivalent to INTERNAL_ERROR.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
 */
enum ErrorCode: int
{
    /**
     * NO_ERROR (0x0): Graceful shutdown.
     *
     * The associated condition is not a result of an error. Used in GOAWAY frames to
     * indicate graceful connection shutdown, or in RST_STREAM frames to indicate that
     * a stream is being cancelled with no error condition.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case NoError = 0x0;

    /**
     * PROTOCOL_ERROR (0x1): Generic protocol violation detected.
     *
     * The endpoint detected an unspecific protocol error. This error is used when a more
     * specific error code is not available. Examples include receiving a frame on a stream
     * in an invalid state, or violating framing requirements.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case ProtocolError = 0x1;

    /**
     * INTERNAL_ERROR (0x2): Implementation fault.
     *
     * The endpoint encountered an unexpected internal error. This is a catch-all for errors
     * that are not caused by protocol violations but by implementation-specific issues such
     * as memory allocation failures or unexpected exceptions.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case InternalError = 0x2;

    /**
     * FLOW_CONTROL_ERROR (0x3): Flow-control limits exceeded.
     *
     * The endpoint detected that its peer violated the flow-control protocol. This occurs
     * when a sender transmits more data than the receiver's advertised flow-control window
     * allows, or when a WINDOW_UPDATE causes the flow-control window to exceed the maximum
     * of 2^31 - 1 octets ({@see MAX_WINDOW_SIZE}).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case FlowControlError = 0x3;

    /**
     * SETTINGS_TIMEOUT (0x4): SETTINGS acknowledgement not received in time.
     *
     * The endpoint sent a SETTINGS frame ({@see FrameType::Settings}) but did not receive
     * an acknowledgement (ACK) in a timely manner. This is a connection error that results
     * in the connection being closed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case SettingsTimeout = 0x4;

    /**
     * STREAM_CLOSED (0x5): Frame received on a closed stream.
     *
     * The endpoint received a frame after the stream was half-closed (the sending endpoint)
     * or fully closed. This indicates the peer is sending frames on a stream that is no
     * longer active. This is a stream error.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case StreamClosed = 0x5;

    /**
     * FRAME_SIZE_ERROR (0x6): Invalid frame size.
     *
     * The endpoint received a frame with an invalid size. This can be a frame that does
     * not conform to the fixed size defined for its type (e.g., PRIORITY must be 5 octets),
     * a frame that exceeds the SETTINGS_MAX_FRAME_SIZE ({@see Setting::MaxFrameSize}),
     * or a frame that is too small to contain mandatory fields.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case FrameSizeError = 0x6;

    /**
     * REFUSED_STREAM (0x7): Stream refused before application processing.
     *
     * The endpoint refused the stream prior to performing any application processing.
     * This indicates that the stream was not processed and the request can be safely
     * retried. The client MAY retry the request on a new stream or a new connection.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case RefusedStream = 0x7;

    /**
     * CANCEL (0x8): Stream is no longer needed.
     *
     * The endpoint uses this to indicate that the stream is no longer needed. In the
     * client context, this means the server response is no longer of interest. In the
     * server context, this means the server is unable or unwilling to complete the response.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case Cancel = 0x8;

    /**
     * COMPRESSION_ERROR (0x9): HPACK decompression failure.
     *
     * The endpoint is unable to maintain the field section compression context
     * (HPACK dynamic table) for the connection. This is a connection error because
     * HPACK state is shared across the entire connection, so a single decompression
     * failure renders all subsequent header blocks unreliable.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case CompressionError = 0x9;

    /**
     * CONNECT_ERROR (0xa): TCP connection established via CONNECT failed.
     *
     * The connection established in response to a CONNECT request (RFC 9113 Section 8.5)
     * was reset or abnormally closed. This error is specific to the tunneled connection
     * and indicates a failure at the transport level of the proxied connection.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case ConnectError = 0xa;

    /**
     * ENHANCE_YOUR_CALM (0xb): Peer is generating excessive load.
     *
     * The endpoint detected that its peer is exhibiting behavior that might be generating
     * excessive load. This is an advisory signal that the peer should reduce its activity.
     * Examples include sending too many empty or small frames, making too many concurrent
     * stream attempts, or compressing overly large headers.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case EnhanceYourCalm = 0xb;

    /**
     * INADEQUATE_SECURITY (0xc): Transport layer does not meet minimum security requirements.
     *
     * The underlying transport has properties that do not meet the minimum security
     * requirements defined in RFC 9113 Section 9.2. For TLS-based deployments, this
     * includes failure to negotiate TLS 1.2 or higher, use of prohibited cipher suites,
     * or insufficient key lengths.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-9.2
     */
    case InadequateSecurity = 0xc;

    /**
     * HTTP_1_1_REQUIRED (0xd): Endpoint requires HTTP/1.1 for this request.
     *
     * The endpoint requires that HTTP/1.1 be used instead of HTTP/2. This error code
     * can be sent in a GOAWAY frame to indicate that the server prefers to use HTTP/1.1
     * for the connection, or in a RST_STREAM frame to indicate that a specific request
     * must use HTTP/1.1. Typically used when an HTTP/2 proxy needs to forward a request
     * to an origin server that only supports HTTP/1.1.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-7
     */
    case HTTP11Required = 0xd;
}
