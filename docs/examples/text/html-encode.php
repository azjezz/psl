<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Html;

Html\encode('<p>"Hello" & welcome</p>');
// '&lt;p&gt;&quot;Hello&quot; &amp; welcome&lt;/p&gt;'

// encode_special_characters is lighter -- only converts &, ", ', <, >
Html\encode_special_characters('<script>alert("xss")</script>');

// '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;'
