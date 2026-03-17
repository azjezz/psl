<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URL;

$url = URL\parse('https://example.com:443/path?q=1#frag');

// "https"
IO\write_line('%s', $url->scheme);
// "example.com"
IO\write_line('%s', $url->authority->host->toString());
// null — default port stripped
IO\write_line('%s', $url->authority->port === null ? 'null' : (string) $url->authority->port);
// "/path"
IO\write_line('%s', $url->path);
// "q=1"
IO\write_line('%s', $url->query ?? '<unknown>');
// "frag"
IO\write_line('%s', $url->fragment ?? '<unknown>');

// "https://example.com/path?q=1#frag"
IO\write_line('%s', $url->toString());
