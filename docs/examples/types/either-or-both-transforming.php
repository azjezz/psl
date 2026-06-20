<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;
use Psl\Str;

$both = new EitherOrBoth\Both::<string, string>('hello', 'world');

// mapLeft / mapRight: transform one side, leave the other untouched
$both->mapLeft::<string>(Str\uppercase(...)); // Both('HELLO', 'world')
$both->mapRight::<string>(Str\uppercase(...)); // Both('hello', 'WORLD')

// mapAny: two closures, one per side. On Both, both run.
$both->mapAny::<string, int>(Str\uppercase(...), Str\length(...)); // Both('HELLO', 5)

// map: same closure applied to whichever side(s) are present.
// On Both, the closure runs twice.
$both->map::<string>(Str\uppercase(...)); // Both('HELLO', 'WORLD')

// mapLeft on a Right is a no-op and returns the same instance:
$right = new EitherOrBoth\Right::<string>('unchanged');
$right->mapLeft::<string>(Str\uppercase(...)); // still Right('unchanged'), same object
