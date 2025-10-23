# Contributing to the PHP Standard Library

Thank you for contributing to the PHP Standard Library!

## Code of Conduct

The code of conduct is described in [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

## Issues

We use GitHub issues to track issues within PSL.

Please ensure your description is clear and has sufficient instructions to be able to reproduce the issue.

## Getting started

Contributing to open-source can be scary. Don't be afraid!
We are looking forward working together to improve this package!

Here is a small checklist to get you going:

- Discuss the changes you want to make first!
- Create a fork of this repository.
- Clone your own repository.
- Run `just install` to get everything set-up for you.
- Checkout a new branch and make the changes you want to make.
- Run `just verify` to verify your code is ok to submit.
- Submit your Pull Request.

## Submitting Pull Requests

Before we can merge your Pull-Request, here are some guidelines that you need to follow.

These guidelines exist not to annoy you, but to keep the code base clean, unified and future proof.

### Principles

- All functions should be typed as strictly as possible
- The library should be internally consistent
- References may not be used
- Arguments should be as general as possible. For example, for `array` functions, prefer `iterable` inputs where practical, falling back to `array` when needed.
- Return types should be as specific as possible
- All files should contain `declare(strict_types=1);`

### Consistency Rules

This is not exhaustive list.

- Functions argument order should be consistent within the library
  - All iterable-related functions take the iterable as the first argument ( e.g. `Dict\map` and `Dict\filter` )
  - `$haystack`, `$needle`, and `$pattern` are in the same order for all functions that take them
- Functions should be consistently named.
- If an operation can conceivably operate on either keys or values, the default is to operate on the values - the version that operates on keys should have `_key` suffix (e.g. `Iter\last`, `Iter\last_key`, `Iter\contains`, `Iter\contains_key` )
- Iterable functions that do an operation based on a user-supplied keying function for each element should be suffixed with `_by` (e.g. `Vec\sort_by`, `Dict\group_by`, `Math\max_by`)

### Tests

PSL tries to maintain a 100% code coverage, meaning everything within the library *MUST* be tested.

If you are submitting a bug-fix, please add a test case to reproduce the bug.
If you are submitting a new feature, please make sure to add tests for all possible code paths.

To run the tests, use `just test`.

### Code Style

PSL follows a custom set of rules that extend PSR-CS.

To check if your code contains any issues that violate PSL rules, use `just fmt-diff`, and `just lint`.

You may fix many of the issues using `just fix`.

### Static Analysis

PSL uses Mago static analysis tool to avoid type issues within the code base, and to provide a better API
for the end user.

PSL is configured to pass the strictest mago level.

To ensure that your code doesn't contain any type issues, use `just analyze`.

### License

By contributing to the PHP Standard Library ( PSL ), you agree that your contributions will be licensed under the [LICENSE](./LICENSE) file in the root directory of this source tree.

## Security Disclosures

You can read more about how to report security issues in our [Security Policy](./SECURITY.md).
