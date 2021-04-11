<?php

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void {}
/** @return lowercase-string */
function return_lowercase_string(): string { return 'hello'; };

(static function (): void {
    $str = Str\after(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\after_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);
})();
