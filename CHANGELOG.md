# Changelog

## 2.7.0

### features

* feat(encoding): introduce `Base64\Variant` enum to support encoding/decoding different variants - [#408](https://github.com/azjezz/psl/pull/408) by @Gashmob

### fixes, and improvements

* fix(option): return `Option<never>` for `Option::none()` - [#415](https://github.com/azjezz/psl/pull/415) by @devnix
* fix(str): add invariant to avoid unexpected errors when parsing an invalid UTF8 string - [#410](https://github.com/azjezz/psl/pull/410) by @devnix

## 2.6.0

### features

* feat(type): introduce `Type\converted` function - [#405](https://github.com/azjezz/psl/pull/405) by @veewee
* feat(type): introduce `Type\numeric_string` function - [#406](https://github.com/azjezz/psl/pull/406) by @veewee

## 2.5.0

### features

* feat(result): introduce `Result\try_catch` function - [#403](https://github.com/azjezz/psl/pull/403) by @azjezz

### fixes, and improvements

* fix(file): improve consistency when creating files for write-mode - [#401](https://github.com/azjezz/psl/pull/401) by @veewee

## 2.4.1

### fixes, and improvements

* fix(type): un-deprecate `Psl\Type\positive_int` function - [#400](https://github.com/azjezz/psl/pull/400) by @dragosprotung

## 2.4.0

### features

* feat(range): introduced `Psl\Range` component - [#378](https://github.com/azjezz/psl/pull/378) by @azjezz
* feat(str): introduced `Psl\Str\range`, `Psl\Str\Byte\range`, and `Psl\Str\Grapheme\range` functions - [#385](https://github.com/azjezz/psl/pull/385) by @azjezz
* feat(type): introduced `Psl\Type\uint` function - [#393](https://github.com/azjezz/psl/pull/393) by @azjezz
* feat(type): introduced `Psl\Type\i8`, `Psl\Type\i16`, `Psl\Type\i32`, `Psl\Type\i64` functions - [#392](https://github.com/azjezz/psl/pull/392) by @azjezz
* feat(type): introduced `Psl\Type\u8`, `Psl\Type\u16`, `Psl\Type\u32` functions - [#395](https://github.com/azjezz/psl/pull/395) by @KennedyTedesco
* feat(type): introduced `Psl\Type\f32`, and `Psl\Type\f64` functions - [#396](https://github.com/azjezz/psl/pull/396) by @KennedyTedesco
* feat(type): introduced `Psl\Type\nonnull` function - [#392](https://github.com/azjezz/psl/pull/392) by @azjezz
* feat(option): improve options type declarations and add `andThen` method - [#398](https://github.com/azjezz/psl/pull/398) by @veewee

### fixes, and improvements

* fix(vec/dict): Return might be non-empty-list/non-empty-array for map functions - [#384](https://github.com/azjezz/psl/pull/384) by @dragosprotung

### other

* chore(async): add async component documentation - [#386](https://github.com/azjezz/psl/pull/386) by @azjezz

### deprecations

* deprecated `Psl\Type\positive_int` function, use `Psl\Type\uint` instead - by @azjezz

## 2.3.1

### fixes, and improvements

* fix(vec): `Vec\reproduce` and `Vec\range` return type is always non-empty-list - [#383](https://github.com/azjezz/psl/pull/383) by @dragosprotung

### other

* chore: update license copyright year - [#371](https://github.com/azjezz/psl/pull/371) by @azjezz

## 2.3.0

### other

* chore: support psalm v5 - [#369](https://github.com/azjezz/psl/pull/369) by @veewee


## 2.2.0

### features

* feat(option): introduce option component - [#356](https://github.com/azjezz/psl/pull/356) by @azjezz

## 2.1.0

### features

* introduced a new `Psl\Type\unit_enum` function - [@19d1230](https://github.com/azjezz/psl/commit/19d123074546cc3ebfca18ad666f100e7fad0658) by @azjezz
* introduced a new `Psl\Type\backed_enum` function - [@19d1230](https://github.com/azjezz/psl/commit/19d123074546cc3ebfca18ad666f100e7fad0658) by @azjezz
* introduced a new `Psl\Type\mixed_vec` function - [#362](https://github.com/azjezz/psl/pull/362) by @BackEndTea
* introduced a new `Psl\Type\mixed_dict` function - [#362](https://github.com/azjezz/psl/pull/362) by @BackEndTea

### fixes, and improvements

* improved `Psl\Type\vec` performance - [#364](https://github.com/azjezz/psl/pull/364) by @BackEndTea
* improved `Psl\Type\float`, and `Psl\Type\num` - [#367](https://github.com/azjezz/psl/pull/367) by @bcremer

### other

* updated `revolt-php/event-loop` to `1.0.0` - [@c7bf866](https://github.com/azjezz/psl/commit/c7bf866a362b9528934a758981da718408ec15d4) by @azjezz
* introduced scope-able loader - [#361](https://github.com/azjezz/psl/pull/361) by @veewee
* fixed wrong function names in examples - [#354](https://github.com/azjezz/psl/pull/354) by @jrmajor
* added reference to PHPStan integration in README.md - [#353](https://github.com/azjezz/psl/pull/353) by @ondrejmirtes

## 2.0.0

* **BC** - removed `Psl\Arr` component.
* **BC** - removed `Psl\Type\is_array`, `Psl\Type\is_arraykey`, `Psl\Type\is_bool`, `Psl\Type\is_callable`, `Psl\Type\is_float`, `Psl\Type\is_instanceof`, `Psl\Type\is_int`, `Psl\Type\is_iterable`, `Psl\Type\is_null`, `Psl\Type\is_numeric`, `Psl\Type\is_object`, `Psl\Type\is_resource`, `Psl\Type\is_scalar`, and `Psl\Type\is_string` functions ( use `TypeInterface::matches($value)` instead ).
* **BC** - removed `Psl\Iter\chain`, `Psl\Iter\chunk`, `Psl\Iter\chunk_with_keys`, `Psl\Iter\diff_by_key`, `Psl\Iter\drop`, `Psl\Iter\drop_while`, `Psl\Iter\enumerate`, `Psl\Iter\filter`, `Psl\Iter\filter_keys`, `Psl\Iter\filter_nulls`, `Psl\Iter\filter_with_key`, `Psl\Iter\flat_map`, `Psl\Iter\flatten`, `Psl\Iter\flip`, `Psl\Iter\from_entries`, `Psl\Iter\from_keys`, `Psl\Iter\keys`, `Psl\Iter\map`, `Psl\Iter\map_keys`, `Psl\Iter\map_with_key`, `Psl\Iter\merge`, `Psl\Iter\product`, `Psl\Iter\pull`, `Psl\Iter\pull_with_key`, `Psl\Iter\range`, `Psl\Iter\reductions`, `Psl\Iter\reindex`, `Psl\Iter\repeat`, `Psl\Iter\reproduce`, `Psl\Iter\reverse`, `Psl\Iter\slice`, `Psl\Iter\take`, `Psl\Iter\take_while`, `Psl\Iter\to_array`, `Psl\Iter\to_array_with_keys`, `Psl\Iter\values`, and `Psl\Iter\zip` functions.
* **BC** - signature of `Psl\Iter\reduce_keys` function changed from `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk): Ts) $function, Ts $initial): Ts`.
* **BC** - signature of `Psl\Iter\reduce_with_keys` function changed from `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk, Tv): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk, Tv): Ts) $function, Ts $initial): Ts`.
* **BC** - removed bundled psalm plugin `Psl\Integration\Psalm\Plugin`, use `php-standard-library/psalm-plugin` package instead.
* dropped support for PHP 8.0
* **BC** - signature of `Psl\Type\object` function changed from `object<T of object>(classname<T> $classname): TypeInterface<T>` to `object(): TypeInterface<object>` ( to preserve the old behavior, use `Psl\Type\instance_of` )
* introduced `Psl\Type\instance_of` function, with the signature of `instance_of<T of object>(classname<T> $classname): TypeInterface<T>`.
* introduced a new `Psl\Async` component.
* refactored `Psl\IO` handles API.
* introduced a new `Psl\File` component.
* refactor `Psl\Shell\execute` to use `Psl\IO` component.
* introduced a `Psl\IO\pipe(): (Psl\IO\CloseReadHandleInterface, Psl\IO\CloseWriteHandleInterface)` function to create a pair of handles, where writes to the WriteHandle can be read from the ReadHandle.
* **BC** - `$encoding` argument for `Psl\Str` functions now accepts `Psl\Str\Encoding` instead of `?string`.
* introduced a new `Psl\Runtime` component.
* introduced a new `Psl\Network` component.
* introduced a new `Psl\TCP` component.
* introduced a new `Psl\Unix` component.
* introduced a new `Psl\Channel` component.
* introduced a new `IO\write()` function.
* introduced a new `IO\write_line()` function.
* introduced a new `IO\write_error()` function.
* introduced a new `IO\write_error_line()` functions.
* introduced a new `Psl\Html\Encoding` enum.
* **BC** - `$encoding` argument for `Psl\Html` functions now accepts `Psl\Html\Encoding` instead of `?string`.
* **BC** - `Psl\Shell\escape_command` function has been removed, no replacement is available.
* introduced a new `Psl\Math\acos` function.
* introduced a new `Psl\Math\asin` function.
* introduced a new `Psl\Math\atan` function.
* introduced a new `Psl\Math\atan2` function.
* **BC** - The type of the $numbers argument of `Psl\Math\mean` has changed to `list<int|float>` instead of `iterable<int|float>`.
* **BC** - The type of the $numbers argument of `Psl\Math\median` has changed to `list<int|float>` instead of `iterable<int|float>`.
* introduced a new `Psl\Promise` component.
* **BC** - `Psl\Result\ResultInterface` now implements `Psl\Promise\PromiseInterface`
* **BC** - `Psl\Type\resource('curl')->toString()` now uses PHP built-in resource kind notation ( i.e: `resource (curl)` ) instead of generic notation ( i.e: `resource<curl>` )
* **BC** - `Psl\Str`, `Psl\Str\Byte`, and `Psl\Str\Grapheme` functions now throw `Psl\Str\Exception\OutOfBoundsException` instead of `Psl\Exception\InvaraintViolationsException` when `$offset` is out-of-bounds.
* **BC** - `Psl\Collection\IndexAccessInterface::at()` now throw `Psl\Collection\Exception\OutOfBoundsException` instead of `Psl\Exception\InvariantViolationException` if `$k` is out-of-bounds.
* **BC** - `Psl\Collection\AccessibleCollectionInterface::slice` signature has changed from `slice(int $start, int $length): static` to `slice(int $start, ?int $length = null): static`
* **BC** - All psl functions previously accepting `callable`, now accept only `Closure`.
* **BC** - `Psl\DataStructure\QueueInterface::dequeue`, and `Psl\DataStructure\StackInterface::pop` now throw `Psl\DataStructure\Exception\UnderflowException` instead of `Psl\Exception\InvariantViolationException` when the data structure is empty.
* **BC** - `Psl\Filesystem\write_file($file, $content)` function has been removed, use `Psl\File\write($file, $content);` instead. 
  > To preserve the same behavior as the old function, use `Psl\File\write($file, $content, Filesystem\is_file($file) ? File\WriteMode::TRUNCATE : File\WriteMode::OPEN_OR_CREATE)`.
* **BC** - `Psl\Filesystem\read_file($file, $offset, $length)` function has been removed, use `Psl\File\read($file, $offset, $length)` instead.
* **BC** - `Psl\Filesystem\append_file($file, $contents)` function has been removed, use `Psl\File\write($file, $contents, File\WriteMode::APPEND)` instead.
* **BC** - `Psl\Filesystem` functions no longer throw `Psl\Exception\InvariantViolationException`.

  New exceptions:
  - `Psl\Filesystem\Exception\NotReadableException` thrown when attempting to read from a non-readable node
  - `Psl\Filesystem\Exception\NotFileException` thrown when attempting a file operation on a non-file node.
  - `Psl\Filesystem\Exception\NotDirectoryException` thrown when attempting a directory operation on a non-directory node.
  - `Psl\Filesystem\Exception\NotSymbolicLinkException` thrown when attempting a symbolic link operation on a non-symbolic link node.
  - `Psl\Filesystem\Exception\NotFoundException` thrown when attempting an operation on a non-existing node.
* introduced `Psl\Hash\Algorithm` enum.
* introduced `Psl\Hash\Hmac\Algorithm` enum.
* **BC** - `Psl\Hash\hash`, and `Psl\Hash\Context::forAlgorithm` now take `Psl\Hash\Algorithm` as an algorithm, rather than a string.
* **BC** - `Psl\Hash\Hmac\hash`, and `Psl\Hash\Context::hmac` now take `Psl\Hash\Hmac\Algorithm` as an algorithm, rather than a string.
* **BC** - A new method `chunk(positive-int $size): CollectionInterface` has been added to `Psl\Collection\CollectionInterface`.
* introduced a new `Psl\OS` component.
* introduced `Psl\Password\Algorithm` enum
* **BC** - all constants of `Psl\Password` component has been removed.
* **BC** - function `Psl\Password\algorithms()` have been removed.
* **BC** - `Psl\Result\ResultInterface::getException()` method has been renamed to `Psl\Result\ResultInterface::getThrowable()`
* **BC** - `Psl\Result\wrap` function now catches all `Throwable`s instead of only `Exception`s
* introduced a new `Psl\Result\reflect` function
* **BC** - `Psl\Shell\escape_argument` function has been removed, `Shell\execute` arguments are now always escaped.
* **BC** - `$escape_arguments` argument of `Shell\execute` function has been removed.
* introduced a new `Psl\Shell\ErrorOutputBehavior` enum
* added a new `$error_output_behavior` argument to `Shell\execute` function, which can be used to return the command error output content, as well as the standard output content.
* introduced a new `Psl\Shell\unpack` function to unpack packed result of `Shell\execute` ( see `Psl\Shell\ErrorOutputBehavior::Packed` ).
* introduced a new `Psl\Shell\stream_unpack` function to unpack packed result of `Shell\execute` chunk by chunk, maintaing order ( see `Psl\Shell\ErrorOutputBehavior::Packed` ).
