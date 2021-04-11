<?php

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;

use Psl\Str;

/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void {}
/** @return lowercase-string */
function return_lowercase_string(): string { return 'hello'; };

(static function (): void {
    take_lowercase_string(
        Str\slice(return_lowercase_string(), 3, 5)
    );

    take_lowercase_string(
        Str\Byte\slice(return_lowercase_string(), 3, 5)
    );

    take_lowercase_string(
        Str\Grapheme\slice(return_lowercase_string(), 3, 5)
    );
})();
