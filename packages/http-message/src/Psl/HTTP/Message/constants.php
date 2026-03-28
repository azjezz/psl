<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

/**
 * 100 Continue.
 *
 * Indicates that the initial part of a request has been received and has not
 * yet been rejected by the server. The client should continue sending the
 * request body or, if the request has already been completed, ignore this
 * response. This is typically used with the "Expect: 100-continue" header.
 *
 * @var int<100, 100>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.2.1
 *
 * @api
 */
const STATUS_CONTINUE = 100;

/**
 * 101 Switching Protocols.
 *
 * Indicates that the server understands and is willing to comply with the
 * client's request to switch protocols via the Upgrade header field. This
 * is used for protocol upgrades such as switching from HTTP/1.1 to WebSocket.
 * Not applicable to HTTP/2 or HTTP/3 connections.
 *
 * @var int<101, 101>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.2.2
 *
 * @api
 */
const STATUS_SWITCHING_PROTOCOLS = 101;

/**
 * 102 Processing (WebDAV).
 *
 * Indicates that the server has received and is processing the request, but
 * no response is available yet. Prevents the client from timing out and
 * assuming the request was lost. Defined by WebDAV (RFC 2518).
 *
 * @var int<102, 102>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2518#section-10.1
 *
 * @api
 */
const STATUS_PROCESSING = 102;

/**
 * 103 Early Hints.
 *
 * Indicates that the server is sending preliminary response headers before
 * the final response. Primarily used to send Link headers so the client can
 * begin preloading resources while the server prepares the final response.
 *
 * @var int<103, 103>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8297
 *
 * @api
 */
const STATUS_EARLY_HINTS = 103;

/**
 * 200 OK.
 *
 * Indicates that the request has succeeded. The meaning of the success
 * depends on the HTTP method: GET returns the target resource, HEAD returns
 * the headers without a body, POST indicates the result of the action, etc.
 *
 * @var int<200, 200>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.1
 *
 * @api
 */
const STATUS_OK = 200;

/**
 * 201 Created.
 *
 * Indicates that the request has been fulfilled and has resulted in one or
 * more new resources being created. The primary resource created is
 * identified by a Location header field in the response.
 *
 * @var int<201, 201>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.2
 *
 * @api
 */
const STATUS_CREATED = 201;

/**
 * 202 Accepted.
 *
 * Indicates that the request has been accepted for processing, but the
 * processing has not been completed. The request might or might not
 * eventually be acted upon, as it might be disallowed when processing
 * actually takes place.
 *
 * @var int<202, 202>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.3
 *
 * @api
 */
const STATUS_ACCEPTED = 202;

/**
 * 203 Non-Authoritative Information.
 *
 * Indicates that the request was successful but the enclosed payload has
 * been modified from that of the origin server's 200 OK response by a
 * transforming proxy.
 *
 * @var int<203, 203>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.4
 *
 * @api
 */
const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;

/**
 * 204 No Content.
 *
 * Indicates that the server has successfully fulfilled the request and there
 * is no additional content to send in the response payload body. Responses
 * with this status code must not include a body.
 *
 * @var int<204, 204>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.5
 *
 * @api
 */
const STATUS_NO_CONTENT = 204;

/**
 * 205 Reset Content.
 *
 * Indicates that the server has fulfilled the request and desires that the
 * user agent reset the document view that caused the request to be sent.
 * Responses with this status code must not include a body.
 *
 * @var int<205, 205>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.6
 *
 * @api
 */
const STATUS_RESET_CONTENT = 205;

/**
 * 206 Partial Content.
 *
 * Indicates that the server is successfully fulfilling a range request for
 * the target resource by transferring one or more parts of the selected
 * representation. Used with the Range and Content-Range headers.
 *
 * @var int<206, 206>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.3.7
 *
 * @api
 */
const STATUS_PARTIAL_CONTENT = 206;

/**
 * 207 Multi-Status (WebDAV).
 *
 * Provides status for multiple independent operations in a single response
 * body, typically encoded as an XML document. Defined by WebDAV (RFC 4918).
 *
 * @var int<207, 207>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-11.1
 *
 * @api
 */
const STATUS_MULTI_STATUS = 207;

/**
 * 208 Already Reported (WebDAV).
 *
 * Used inside a DAV:propstat response element to avoid enumerating the
 * internal members of multiple bindings to the same collection repeatedly.
 * Defined by WebDAV Binding Extensions (RFC 5842).
 *
 * @var int<208, 208>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5842#section-7.1
 *
 * @api
 */
const STATUS_ALREADY_REPORTED = 208;

/**
 * 226 IM Used.
 *
 * Indicates that the server has fulfilled a GET request for the resource,
 * and the response is a representation of the result of one or more
 * instance-manipulations applied to the current instance. Defined by
 * RFC 3229 (Delta Encoding in HTTP).
 *
 * @var int<226, 226>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3229#section-10.4.1
 *
 * @api
 */
const STATUS_IM_USED = 226;

/**
 * 300 Multiple Choices.
 *
 * Indicates that the target resource has more than one representation, each
 * with its own more specific identifier, and the user or user agent can
 * select a preferred representation.
 *
 * @var int<300, 300>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.1
 *
 * @api
 */
const STATUS_MULTIPLE_CHOICES = 300;

/**
 * 301 Moved Permanently.
 *
 * Indicates that the target resource has been assigned a new permanent URI
 * and any future references to this resource should use one of the enclosed
 * URIs. The Location header contains the new URI.
 *
 * @var int<301, 301>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.2
 *
 * @api
 */
const STATUS_MOVED_PERMANENTLY = 301;

/**
 * 302 Found.
 *
 * Indicates that the target resource resides temporarily under a different
 * URI. The client should continue to use the original URI for future requests.
 * The Location header contains the temporary URI.
 *
 * @var int<302, 302>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.3
 *
 * @api
 */
const STATUS_FOUND = 302;

/**
 * 303 See Other.
 *
 * Indicates that the server is redirecting the user agent to a different
 * resource, as indicated by the Location header, which is intended to
 * provide an indirect response to the original request. The client should
 * use GET to retrieve the indicated resource.
 *
 * @var int<303, 303>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.4
 *
 * @api
 */
const STATUS_SEE_OTHER = 303;

/**
 * 304 Not Modified.
 *
 * Indicates that a conditional GET or HEAD request has been received and
 * would have resulted in a 200 OK response if not for the condition
 * evaluating to false. The server is redirecting the client to use a
 * previously cached representation. Responses with this status code must
 * not include a body.
 *
 * @var int<304, 304>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.5
 *
 * @api
 */
const STATUS_NOT_MODIFIED = 304;

/**
 * 305 Use Proxy.
 *
 * Deprecated. Previously indicated that the requested resource must be
 * accessed through the proxy given by the Location header. Due to security
 * concerns, this status code is no longer recommended.
 *
 * @var int<305, 305>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.6
 *
 * @deprecated This status code is deprecated due to security concerns.
 *
 * @api
 */
const STATUS_USE_PROXY = 305;

/**
 * 307 Temporary Redirect.
 *
 * Indicates that the target resource resides temporarily under a different
 * URI and the user agent must not change the request method if it performs
 * an automatic redirection to that URI. Unlike 302, this guarantees the
 * method and body will not be changed when the redirect is followed.
 *
 * @var int<307, 307>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.8
 *
 * @api
 */
const STATUS_TEMPORARY_REDIRECT = 307;

/**
 * 308 Permanent Redirect.
 *
 * Indicates that the target resource has been assigned a new permanent URI
 * and the user agent must not change the request method if it performs an
 * automatic redirection. Similar to 301 but guarantees the method and body
 * are preserved.
 *
 * @var int<308, 308>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.4.9
 *
 * @api
 */
const STATUS_PERMANENT_REDIRECT = 308;

/**
 * 400 Bad Request.
 *
 * Indicates that the server cannot or will not process the request due to
 * something that is perceived to be a client error, such as malformed
 * request syntax, invalid request message framing, or deceptive request
 * routing.
 *
 * @var int<400, 400>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.1
 *
 * @api
 */
const STATUS_BAD_REQUEST = 400;

/**
 * 401 Unauthorized.
 *
 * Indicates that the request has not been applied because it lacks valid
 * authentication credentials for the target resource. The response must
 * include a WWW-Authenticate header field containing at least one challenge
 * applicable to the target resource.
 *
 * @var int<401, 401>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.2
 *
 * @api
 */
const STATUS_UNAUTHORIZED = 401;

/**
 * 402 Payment Required.
 *
 * Reserved for future use. The original intention was that this code might
 * be used as part of some form of digital cash or micropayment scheme, but
 * that has not yet happened.
 *
 * @var int<402, 402>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.3
 *
 * @api
 */
const STATUS_PAYMENT_REQUIRED = 402;

/**
 * 403 Forbidden.
 *
 * Indicates that the server understood the request but refuses to fulfill
 * it. Unlike 401, authenticating will not make a difference. The request
 * should not be repeated.
 *
 * @var int<403, 403>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.4
 *
 * @api
 */
const STATUS_FORBIDDEN = 403;

/**
 * 404 Not Found.
 *
 * Indicates that the origin server did not find a current representation
 * for the target resource or is not willing to disclose that one exists.
 *
 * @var int<404, 404>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.5
 *
 * @api
 */
const STATUS_NOT_FOUND = 404;

/**
 * 405 Method Not Allowed.
 *
 * Indicates that the method received in the request line is known by the
 * origin server but not supported by the target resource. The response must
 * include an Allow header containing the list of methods supported by the
 * target resource.
 *
 * @var int<405, 405>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.6
 *
 * @api
 */
const STATUS_METHOD_NOT_ALLOWED = 405;

/**
 * 406 Not Acceptable.
 *
 * Indicates that the target resource does not have a current representation
 * that would be acceptable to the user agent, according to the proactive
 * negotiation header fields received in the request (Accept, Accept-Language,
 * etc.).
 *
 * @var int<406, 406>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.7
 *
 * @api
 */
const STATUS_NOT_ACCEPTABLE = 406;

/**
 * 407 Proxy Authentication Required.
 *
 * Similar to 401, but indicates that the client needs to authenticate itself
 * in order to use a proxy. The response must include a Proxy-Authenticate
 * header field containing a challenge applicable to the proxy.
 *
 * @var int<407, 407>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.8
 *
 * @api
 */
const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;

/**
 * 408 Request Timeout.
 *
 * Indicates that the server did not receive a complete request message
 * within the time that it was prepared to wait. The client may repeat the
 * request without modifications at any later time.
 *
 * @var int<408, 408>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.9
 *
 * @api
 */
const STATUS_REQUEST_TIMEOUT = 408;

/**
 * 409 Conflict.
 *
 * Indicates that the request could not be completed due to a conflict with
 * the current state of the target resource. This is typically used in
 * situations where the user might be able to resolve the conflict and
 * resubmit the request.
 *
 * @var int<409, 409>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.10
 *
 * @api
 */
const STATUS_CONFLICT = 409;

/**
 * 410 Gone.
 *
 * Indicates that access to the target resource is no longer available at
 * the origin server and this condition is likely to be permanent. Unlike
 * 404, this indicates the server is aware that the resource previously
 * existed and has been intentionally removed.
 *
 * @var int<410, 410>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.11
 *
 * @api
 */
const STATUS_GONE = 410;

/**
 * 411 Length Required.
 *
 * Indicates that the server refuses to accept the request without a
 * defined Content-Length header field.
 *
 * @var int<411, 411>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.12
 *
 * @api
 */
const STATUS_LENGTH_REQUIRED = 411;

/**
 * 412 Precondition Failed.
 *
 * Indicates that one or more conditions given in the request header fields
 * evaluated to false when tested on the server. Used with conditional
 * requests (If-Match, If-Unmodified-Since, etc.).
 *
 * @var int<412, 412>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.13
 *
 * @api
 */
const STATUS_PRECONDITION_FAILED = 412;

/**
 * 413 Content Too Large.
 *
 * Indicates that the server is refusing to process a request because the
 * request content is larger than the server is willing or able to process.
 * Previously known as "Request Entity Too Large" (RFC 7231).
 *
 * @var int<413, 413>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.14
 *
 * @api
 */
const STATUS_CONTENT_TOO_LARGE = 413;

/**
 * 414 URI Too Long.
 *
 * Indicates that the server is refusing to service the request because the
 * target URI is longer than the server is willing to interpret.
 *
 * @var int<414, 414>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.15
 *
 * @api
 */
const STATUS_URI_TOO_LONG = 414;

/**
 * 415 Unsupported Media Type.
 *
 * Indicates that the origin server is refusing to service the request
 * because the content is in a format not supported by this method on the
 * target resource.
 *
 * @var int<415, 415>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.16
 *
 * @api
 */
const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;

/**
 * 416 Range Not Satisfiable.
 *
 * Indicates that the set of ranges in the request's Range header field has
 * been rejected because none of the requested ranges are satisfiable for
 * the target resource.
 *
 * @var int<416, 416>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.17
 *
 * @api
 */
const STATUS_RANGE_NOT_SATISFIABLE = 416;

/**
 * 417 Expectation Failed.
 *
 * Indicates that the expectation given in the request's Expect header field
 * could not be met by at least one of the inbound servers.
 *
 * @var int<417, 417>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.18
 *
 * @api
 */
const STATUS_EXPECTATION_FAILED = 417;

/**
 * 418 I'm a Teapot.
 *
 * Any attempt to brew coffee with a teapot should result in this error code.
 * Originally defined by the Hyper Text Coffee Pot Control Protocol (RFC 2324)
 * as an April Fools' joke, this status code has become a well-known Easter egg
 * in HTTP implementations.
 *
 * @var int<418, 418>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2324#section-2.3.2
 *
 * @api
 */
const STATUS_IM_A_TEAPOT = 418;

/**
 * 421 Misdirected Request.
 *
 * Indicates that the request was directed at a server that is not able to
 * produce a response. This can be sent by a server that is not configured
 * to produce responses for the combination of scheme and authority included
 * in the request URI. Particularly relevant for HTTP/2 connections where
 * multiple hostnames may share a connection.
 *
 * @var int<421, 421>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.20
 *
 * @api
 */
const STATUS_MISDIRECTED_REQUEST = 421;

/**
 * 422 Unprocessable Content.
 *
 * Indicates that the server understands the content type of the request
 * content and the syntax of the request content is correct, but it was
 * unable to process the contained instructions. Previously known as
 * "Unprocessable Entity" (RFC 4918).
 *
 * @var int<422, 422>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.21
 *
 * @api
 */
const STATUS_UNPROCESSABLE_CONTENT = 422;

/**
 * 423 Locked (WebDAV).
 *
 * Indicates that the source or destination resource of a method is locked.
 * Defined by WebDAV (RFC 4918).
 *
 * @var int<423, 423>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-11.3
 *
 * @api
 */
const STATUS_LOCKED = 423;

/**
 * 424 Failed Dependency (WebDAV).
 *
 * Indicates that the method could not be performed on the resource because
 * the requested action depended on another action that failed. Defined by
 * WebDAV (RFC 4918).
 *
 * @var int<424, 424>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-11.4
 *
 * @api
 */
const STATUS_FAILED_DEPENDENCY = 424;

/**
 * 425 Too Early.
 *
 * Indicates that the server is unwilling to risk processing a request that
 * might be replayed. Used to protect against replay attacks when requests
 * are sent in TLS early data (0-RTT).
 *
 * @var int<425, 425>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8470
 *
 * @api
 */
const STATUS_TOO_EARLY = 425;

/**
 * 426 Upgrade Required.
 *
 * Indicates that the server refuses to perform the request using the
 * current protocol but might be willing to do so after the client upgrades
 * to a different protocol. The server must include an Upgrade header field
 * indicating the required protocol(s).
 *
 * @var int<426, 426>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.5.22
 *
 * @api
 */
const STATUS_UPGRADE_REQUIRED = 426;

/**
 * 428 Precondition Required.
 *
 * Indicates that the origin server requires the request to be conditional.
 * Intended to prevent the "lost update" problem, where a client GETs a
 * resource, modifies it, and PUTs it back while a third party has modified
 * it on the server, leading to a lost modification.
 *
 * @var int<428, 428>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6585#section-3
 *
 * @api
 */
const STATUS_PRECONDITION_REQUIRED = 428;

/**
 * 429 Too Many Requests.
 *
 * Indicates that the user has sent too many requests in a given amount of
 * time (rate limiting). The response may include a Retry-After header
 * indicating how long the client should wait before making a new request.
 *
 * @var int<429, 429>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6585#section-4
 *
 * @api
 */
const STATUS_TOO_MANY_REQUESTS = 429;

/**
 * 431 Request Header Fields Too Large.
 *
 * Indicates that the server is unwilling to process the request because
 * its header fields are too large. The request may be resubmitted after
 * reducing the size of the request header fields.
 *
 * @var int<431, 431>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6585#section-5
 *
 * @api
 */
const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

/**
 * 451 Unavailable For Legal Reasons.
 *
 * Indicates that the server is denying access to the resource as a
 * consequence of a legal demand. The response should include an explanation
 * in the response body of the legal demand and the party making it.
 *
 * @var int<451, 451>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7725
 *
 * @api
 */
const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

/**
 * 500 Internal Server Error.
 *
 * Indicates that the server encountered an unexpected condition that
 * prevented it from fulfilling the request. This is a generic catch-all
 * error response.
 *
 * @var int<500, 500>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.1
 *
 * @api
 */
const STATUS_INTERNAL_SERVER_ERROR = 500;

/**
 * 501 Not Implemented.
 *
 * Indicates that the server does not support the functionality required to
 * fulfill the request. This is the appropriate response when the server
 * does not recognize the request method.
 *
 * @var int<501, 501>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.2
 *
 * @api
 */
const STATUS_NOT_IMPLEMENTED = 501;

/**
 * 502 Bad Gateway.
 *
 * Indicates that the server, while acting as a gateway or proxy, received
 * an invalid response from an inbound server it accessed while attempting
 * to fulfill the request.
 *
 * @var int<502, 502>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.3
 *
 * @api
 */
const STATUS_BAD_GATEWAY = 502;

/**
 * 503 Service Unavailable.
 *
 * Indicates that the server is currently unable to handle the request due
 * to a temporary overload or scheduled maintenance. The response may include
 * a Retry-After header indicating how long the client should wait.
 *
 * @var int<503, 503>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.4
 *
 * @api
 */
const STATUS_SERVICE_UNAVAILABLE = 503;

/**
 * 504 Gateway Timeout.
 *
 * Indicates that the server, while acting as a gateway or proxy, did not
 * receive a timely response from an upstream server it needed to access
 * in order to complete the request.
 *
 * @var int<504, 504>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.5
 *
 * @api
 */
const STATUS_GATEWAY_TIMEOUT = 504;

/**
 * 505 HTTP Version Not Supported.
 *
 * Indicates that the server does not support, or refuses to support, the
 * major version of HTTP that was used in the request message.
 *
 * @var int<505, 505>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-15.6.6
 *
 * @api
 */
const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;

/**
 * 506 Variant Also Negotiates.
 *
 * Indicates that the server has an internal configuration error: the chosen
 * variant resource is configured to engage in transparent content
 * negotiation itself, and is therefore not a proper end point in the
 * negotiation process.
 *
 * @var int<506, 506>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2295#section-8.1
 *
 * @api
 */
const STATUS_VARIANT_ALSO_NEGOTIATES = 506;

/**
 * 507 Insufficient Storage (WebDAV).
 *
 * Indicates that the method could not be performed on the resource because
 * the server is unable to store the representation needed to successfully
 * complete the request. Defined by WebDAV (RFC 4918).
 *
 * @var int<507, 507>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-11.5
 *
 * @api
 */
const STATUS_INSUFFICIENT_STORAGE = 507;

/**
 * 508 Loop Detected (WebDAV).
 *
 * Indicates that the server terminated an operation because it encountered
 * an infinite loop while processing a request with "Depth: infinity".
 * Defined by WebDAV Binding Extensions (RFC 5842).
 *
 * @var int<508, 508>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5842#section-7.2
 *
 * @api
 */
const STATUS_LOOP_DETECTED = 508;

/**
 * 510 Not Extended.
 *
 * Indicates that further extensions to the request are required for the
 * server to fulfill it. Defined by RFC 2774 (HTTP Extension Framework).
 *
 * @var int<510, 510>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2774#section-7
 *
 * @deprecated This status code is obsoleted by RFC 9110.
 *
 * @api
 */
const STATUS_NOT_EXTENDED = 510;

/**
 * 511 Network Authentication Required.
 *
 * Indicates that the client needs to authenticate to gain network access.
 * Used by intercepting proxies (captive portals) to indicate that access
 * to the network requires authentication.
 *
 * @var int<511, 511>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6585#section-6
 *
 * @api
 */
const STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511;

/**
 * GET method.
 *
 * Requests transfer of a current selected representation for the target
 * resource. GET is the primary mechanism of information retrieval and the
 * focus of almost all performance optimizations.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.1
 *
 * @api
 */
const METHOD_GET = 'GET';

/**
 * HEAD method.
 *
 * Identical to GET except that the server must not send content in the
 * response. Used to obtain metadata about a resource without transferring
 * the representation data.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.2
 *
 * @api
 */
const METHOD_HEAD = 'HEAD';

/**
 * POST method.
 *
 * Requests that the target resource process the representation enclosed
 * in the request according to the resource's own specific semantics.
 * Commonly used for form submissions, file uploads, and creating new
 * resources.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.3
 *
 * @api
 */
const METHOD_POST = 'POST';

/**
 * PUT method.
 *
 * Requests that the state of the target resource be created or replaced
 * with the state defined by the representation enclosed in the request
 * message content.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.4
 *
 * @api
 */
const METHOD_PUT = 'PUT';

/**
 * DELETE method.
 *
 * Requests that the origin server remove the association between the
 * target resource and its current functionality.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.5
 *
 * @api
 */
const METHOD_DELETE = 'DELETE';

/**
 * CONNECT method.
 *
 * Requests that the recipient establish a tunnel to the destination origin
 * server identified by the request target, and if successful, thereafter
 * restrict its behavior to blind forwarding of data in both directions.
 * Used for TLS tunneling through HTTP proxies.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.6
 *
 * @api
 */
const METHOD_CONNECT = 'CONNECT';

/**
 * OPTIONS method.
 *
 * Requests information about the communication options available for the
 * target resource. Used in CORS preflight requests and for discovering
 * server capabilities.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.7
 *
 * @api
 */
const METHOD_OPTIONS = 'OPTIONS';

/**
 * TRACE method.
 *
 * Requests a remote, application-level loop-back of the request message.
 * The final recipient should reflect the received message back to the
 * client as the content of a 200 response. Used for diagnostics.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.8
 *
 * @api
 */
const METHOD_TRACE = 'TRACE';

/**
 * PATCH method.
 *
 * Requests that a set of changes described in the request content be
 * applied to the target resource. Unlike PUT, which replaces the entire
 * resource, PATCH applies a partial modification.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5789
 *
 * @api
 */
const METHOD_PATCH = 'PATCH';

/**
 * COPY method (WebDAV).
 *
 * Requests that the server create a duplicate of the source resource
 * identified by the request URI at the destination URI specified in the
 * Destination header. Defined by WebDAV (RFC 4918).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-9.8
 *
 * @api
 */
const METHOD_COPY = 'COPY';

/**
 * MOVE method (WebDAV).
 *
 * Requests that the server move the source resource identified by the
 * request URI to the destination URI specified in the Destination header.
 * Equivalent to a COPY followed by a DELETE of the source. Defined by
 * WebDAV (RFC 4918).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-9.9
 *
 * @api
 */
const METHOD_MOVE = 'MOVE';

/**
 * LOCK method (WebDAV).
 *
 * Requests that the server apply a lock to the resource identified by the
 * request URI. Locks prevent other users from modifying the resource.
 * Defined by WebDAV (RFC 4918).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-9.10
 *
 * @api
 */
const METHOD_LOCK = 'LOCK';

/**
 * UNLOCK method (WebDAV).
 *
 * Requests that the server remove the lock identified by the lock token
 * in the Lock-Token header from the resource identified by the request URI.
 * Defined by WebDAV (RFC 4918).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4918#section-9.11
 *
 * @api
 */
const METHOD_UNLOCK = 'UNLOCK';

/**
 * REPORT method (WebDAV).
 *
 * Requests that the server generate a report based on the report
 * specification in the request body. Defined by WebDAV Versioning
 * Extensions (RFC 3253).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3253#section-3.6
 *
 * @api
 */
const METHOD_REPORT = 'REPORT';

/**
 * MERGE method (WebDAV).
 *
 * Requests that the server merge changes from the source resource into the
 * target resource. Defined by WebDAV Versioning Extensions (RFC 3253).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3253#section-11.2
 *
 * @api
 */
const METHOD_MERGE = 'MERGE';

/**
 * SEARCH method (WebDAV).
 *
 * Requests that the server execute a search using the query in the request
 * body. Defined by WebDAV SEARCH (RFC 5323).
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5323
 *
 * @api
 */
const METHOD_SEARCH = 'SEARCH';

/**
 * QUERY method.
 *
 * Requests that the server perform a query operation using the content of
 * the request body. Similar to GET but with a request body for expressing
 * complex queries. Defined by the HTTP QUERY Method draft.
 *
 * @var non-empty-uppercase-string
 *
 * @link https://datatracker.ietf.org/doc/html/draft-ietf-httpbis-safe-method-w-body
 *
 * @api
 */
const METHOD_QUERY = 'QUERY';

/**
 * PURGE method.
 *
 * A non-standard method commonly used by caching proxies (such as Varnish
 * and Squid) to request removal of a cached resource. Not defined by any
 * RFC but widely supported in CDN and reverse proxy configurations.
 *
 * @var non-empty-uppercase-string
 *
 * @api
 */
const METHOD_PURGE = 'PURGE';
