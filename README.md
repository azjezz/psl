<p align="center" style="width: 80%; margin: auto;">
  <img src="docs/resources/banner.png" alt="PSL" width="100%">
</p>

# PSL - PHP Standard Library

![Unit tests status](https://github.com/php-standard-library/php-standard-library/workflows/unit%20tests/badge.svg)
![Static analysis status](https://github.com/php-standard-library/php-standard-library/workflows/static%20analysis/badge.svg)
![Coding standards status](https://github.com/php-standard-library/php-standard-library/workflows/coding%20standards/badge.svg)
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/4228/badge)](https://bestpractices.coreinfrastructure.org/projects/4228)
[![Coverage Status](https://coveralls.io/repos/github/php-standard-library/php-standard-library/badge.svg)](https://coveralls.io/github/php-standard-library/php-standard-library)
[![MSI](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphp-standard-library%2Fphp-standard-library%2Fnext)](https://dashboard.stryker-mutator.io/reports/github.com/php-standard-library/php-standard-library/next)
[![Total Downloads](https://poser.pugx.org/php-standard-library/php-standard-library/d/total.svg)](https://packagist.org/packages/php-standard-library/php-standard-library)
[![Latest Stable Version](https://poser.pugx.org/php-standard-library/php-standard-library/v/stable.svg)](https://packagist.org/packages/php-standard-library/php-standard-library)
[![License](https://poser.pugx.org/php-standard-library/php-standard-library/license.svg)](https://packagist.org/packages/php-standard-library/php-standard-library)

A standard library for PHP, inspired by [hhvm/hsl](https://github.com/hhvm/hsl). PSL provides a consistent, centralized, well-typed set of APIs covering async, collections, networking, I/O, cryptography, terminal UI, and more - replacing PHP functions and primitives with safer, async-ready alternatives that error predictably.

**[Documentation](https://php-standard-library.dev)** · **[Sponsor](https://github.com/sponsors/azjezz)**

## Installation

```shell
composer require php-standard-library/php-standard-library
```

Requires PHP 8.4+.

## Quick Look

### Type-safe data validation

Validate and coerce untrusted data with composable type combinators - shapes, unions, optionals - all with zero reflection overhead.

```php
use Psl\Type;

$userType = Type\shape([
    'name' => Type\non_empty_string(),
    'age'  => Type\positive_int(),
    'tags' => Type\vec(Type\string()),
]);

$user = $userType->coerce($untrustedInput);
// array{name: non-empty-string, age: positive-int, tags: list<string>}
```

### Structured concurrency

Run concurrent operations with a single function call. Structured concurrency built on fibers - no promises, no callbacks.

```php
use Psl\Async;
use Psl\TCP;
use Psl\IO;

Async\main(static function(): int {
    [$a, $b] = Async\concurrently([
        static fn() => TCP\connect('api.example.com', 443),
        static fn() => TCP\connect('db.example.com', 5432),
    ]);

    IO\write_error_line('Both connections ready');

    return 0;
});
```

### Functional collections

Map, filter, sort, and reshape arrays with pure functions. Separate return types for lists and dicts - no more array key confusion.

```php
use Psl\Vec;
use Psl\Dict;
use Psl\Str;

$names = ['alice', 'bob', 'charlie'];

Vec\map($names, Str\uppercase(...));
// ['ALICE', 'BOB', 'CHARLIE']

Vec\filter($names, fn($n) => Str\length($n) > 3);
// ['alice', 'charlie']

Dict\pull($names, Str\uppercase(...), fn($n) => $n);
// {alice: 'ALICE', bob: 'BOB', charlie: 'CHARLIE'}
```

### TCP server in 10 lines

Production-ready networking primitives. TCP, TLS, UDP, Unix sockets - all async, all composable.

```php
use Psl\Async;
use Psl\TCP;
use Psl\IO;

Async\main(static function(): int {
    $server = TCP\listen('127.0.0.1', 8080);
    IO\write_error_line('Listening on :8080');

    while (true) {
        $conn = $server->accept();
        Async\run(static function() use ($conn) {
            $conn->writeAll("Hello!\n");
            $conn->close();
        })->ignore();
    }
});
```
## Tooling

| Tool | Description |
|---|---|
| [Mago](https://mago.carthage.software/tools/analyzer/configuration-reference#available-plugins) | Enhanced type inference for Mago |
| [Psalm Plugin](https://github.com/php-standard-library/psalm-plugin) | Enhanced type inference for Psalm |
| [PHPStan Extension](https://github.com/php-standard-library/phpstan-extension) | Enhanced type inference for PHPStan |

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md).

## License

MIT - see [LICENSE](./LICENSE).
