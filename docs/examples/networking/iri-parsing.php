<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\IRI;

$iri = IRI\parse('https://münchen.de/straße?q=ünited#§ion');

// "https"
IO\write_line('%s', $iri->scheme ?? '<unknown>');
// "münchen.de"
IO\write_line('%s', $iri->authority?->host?->toString() ?? '<unknown>');
// "/straße"
IO\write_line('%s', $iri->path);
// "q=ünited"
IO\write_line('%s', $iri->query ?? '<unknown>');
// "§ion"
IO\write_line('%s', $iri->fragment ?? '<unknown>');

// "https://münchen.de/straße?q=ünited#§ion"
IO\write_line('%s', $iri->toString());
