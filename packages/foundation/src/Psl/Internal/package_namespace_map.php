<?php

declare(strict_types=1);

namespace Psl\Internal;

/**
 * Returns the canonical mapping of PSL namespace names to package directory slugs.
 *
 * Used by the splitter's verify command and by `bin/psl detect-packages`.
 *
 * @return array<non-empty-string, non-empty-string>
 *
 * @mago-expect lint:no-literal-password - Not really a password.
 */
function package_namespace_map(): array
{
    return [
        'Ansi' => 'ansi',
        'Async' => 'async',
        'Binary' => 'binary',
        'Cache' => 'cache',
        'Channel' => 'channel',
        'CIDR' => 'cidr',
        'Class' => 'class',
        'Collection' => 'collection',
        'Comparison' => 'comparison',
        'Compression' => 'compression',
        'Crypto' => 'crypto',
        'DataStructure' => 'data-structure',
        'DNS' => 'dns',
        'DNSSEC' => 'dnssec',
        'DateTime' => 'date-time',
        'Default' => 'default',
        'Dict' => 'dict',
        'Either' => 'either',
        'EitherOrBoth' => 'either-or-both',
        'Encoding' => 'encoding',
        'Env' => 'env',
        'File' => 'file',
        'Filesystem' => 'filesystem',
        'Fun' => 'fun',
        'Graph' => 'graph',
        'H2' => 'h2',
        'Hash' => 'hash',
        'HPACK' => 'hpack',
        'HTTP\\Client' => 'http-client',
        'HTTP\\Message' => 'http-message',
        'Html' => 'html',
        'Interface' => 'interface',
        'Interoperability' => 'interoperability',
        'IO' => 'io',
        'IP' => 'ip',
        'IRI' => 'iri',
        'Iter' => 'iter',
        'Json' => 'json',
        'Locale' => 'locale',
        'MIME' => 'mime',
        'Message' => 'message',
        'Math' => 'math',
        'Network' => 'network',
        'Observer' => 'observer',
        'Option' => 'option',
        'OS' => 'os',
        'Password' => 'password',
        'Process' => 'process',
        'Promise' => 'promise',
        'PseudoRandom' => 'pseudo-random',
        'Punycode' => 'punycode',
        'RandomSequence' => 'random-sequence',
        'Range' => 'range',
        'Regex' => 'regex',
        'Result' => 'result',
        'Runtime' => 'runtime',
        'SecureRandom' => 'secure-random',
        'SMTP' => 'smtp',
        'Shell' => 'shell',
        'Socks' => 'socks',
        'Str' => 'str',
        'TCP' => 'tcp',
        'Terminal' => 'terminal',
        'TLS' => 'tls',
        'Trait' => 'trait',
        'Tree' => 'tree',
        'Type' => 'type',
        'UDP' => 'udp',
        'Unix' => 'unix',
        'URI' => 'uri',
        'URL' => 'url',
        'Vec' => 'vec',
        'Exception' => 'foundation',
        'Ref' => 'foundation',
    ];
}
