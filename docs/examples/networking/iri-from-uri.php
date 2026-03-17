<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\IRI;
use Psl\URI;

$uri = URI\parse('https://xn--mnchen-3ya.de/stra%C3%9Fe');

$iri = IRI\from_uri($uri);

// "https://münchen.de/straße"
IO\write_line('%s', $iri->toString());
