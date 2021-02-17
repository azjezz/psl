# Psl - PHP Standard Library

![Unit tests status](https://github.com/azjezz/psl/workflows/unit%20tests/badge.svg)
![Static analysis status](https://github.com/azjezz/psl/workflows/static%20analysis/badge.svg)
![Security analysis status](https://github.com/azjezz/psl/workflows/security%20analysis/badge.svg)
![Coding standards status](https://github.com/azjezz/psl/workflows/coding%20standards/badge.svg)
[![TravisCI Build Status](https://travis-ci.com/azjezz/psl.svg)](https://travis-ci.com/azjezz/psl)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/psl/badge.svg)](https://coveralls.io/github/azjezz/psl)
[![Type Coverage](https://shepherd.dev/github/azjezz/psl/coverage.svg)](https://shepherd.dev/github/azjezz/psl)
[![SymfonyInsight](https://insight.symfony.com/projects/1e053a4a-aaab-4a52-9059-c4883bfd46f7/mini.svg)](https://insight.symfony.com/projects/1e053a4a-aaab-4a52-9059-c4883bfd46f7)
[![Total Downloads](https://poser.pugx.org/azjezz/psl/d/total.svg)](https://packagist.org/packages/azjezz/psl)
[![Latest Stable Version](https://poser.pugx.org/azjezz/psl/v/stable.svg)](https://packagist.org/packages/azjezz/psl)
[![License](https://poser.pugx.org/azjezz/psl/license.svg)](https://packagist.org/packages/azjezz/psl)

Psl is a standard library for PHP, inspired by [hhvm/hsl](https://github.com/hhvm/hsl).

The goal of Psl is to provide a consistent, centralized, well-typed set of APIs for PHP programmers.

## Example
```php
<?php

declare(strict_types=1);

use Psl\{Dict, Str, Vec};

/**
 * @psalm-param iterable<?int> $codes
 */
function foo(iterable $codes): string
{
    $codes = Vec\filter_nulls($codes);

    $chars = Dict\map($codes, fn(int $code): string => Str\chr($code));

    return Str\join($chars, ', ');
}

foo([95, 96, null, 98]);
// 'a, b, d'
```

## Installation

Supported installation method is via [composer](https://getcomposer.org):

```console
$ composer require azjezz/psl
```

### Psalm Integration

PSL comes with a [Psalm](https://psalm.dev/) plugin, that improves return type for PSL functions that psalm cannot infer from source code.

To enable the Psalm plugin, add the `Psl\Integration\Psalm\Plugin` class to your psalm configuration file plugins list as follows:

```xml
<psalm>
   ...
   <plugins>
      ...
      <pluginClass class="Psl\Integration\Psalm\Plugin" />
   </plugins>
</psalm>

```

## Documentation

Documentation is not available yet.

## Principles

 - All functions should be typed as strictly as possible
 - The library should be internally consistent
 - References may not be used
 - Arguments should be as general as possible. For example, for `array` functions, prefer `iterable` inputs where practical, falling back to `array` when needed.
 - Return types should be as specific as possible
 - All files should contain `declare(strict_types=1);`

## Consistency Rules

This is not exhaustive list.

 - Functions argument order should be consistent within the library
   - All iterable-related functions take the iterable as the first argument ( e.g. `Iter\map` and `Iter\filter` )
   - `$haystack`, `$needle`, and `$pattern` are in the same order for all functions that take them
 - Functions should be consistently named.
 - If an operation can conceivably operate on either keys or values, the default is to operate on the values - the version that operates on keys should have `_key` suffix (e.g. `Iter\last`, `Iter\last_key`, `Iter\contains`, `Iter\contains_key` )
 - Find-like operations that can fail should return `?T`; a second function should be added with an `x` suffix that uses an invariant to return `T` (e.g. `Arr\last`, `Arr\lastx`)
 - Iterable functions that do an operation based on a user-supplied keying function for each element should be suffixed with `_by` (e.g. `Arr\sort_by`, `Iter\group_by`, `Math\max_by`)

## Sponsors

Thanks to our sponsors and supporters:


| JetBrains |
|---|
| <a href="https://www.jetbrains.com/?from=PSL ( PHP Standard Library )" title="JetBrains" target="_blank"><img src="https://res.cloudinary.com/azjezz/image/upload/v1599239910/jetbrains_qnyb0o.png" height="120" /></a> |


## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
