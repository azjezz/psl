<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Html;

$html = '<p>"Hello" & welcome</p>';

Html\encode($html, encoding: Html\Encoding::Iso88591);
Html\decode('&lt;p&gt;Hello&lt;/p&gt;', Html\Encoding::ShiftJis);
