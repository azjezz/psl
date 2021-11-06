help:                                                                           ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install-root-dependencies:                                                      ## install dependencies for the library itself
	composer update

install-coding-standard-dependencies: install-root-dependencies                 ## install dependencies for coding-standard checks tooling
	cd tools/php-cs-fixer && composer update --ignore-platform-req php
	cd tools/php-codesniffer && composer install

install-benchmark-dependencies: install-root-dependencies                       ## install dependencies for benchmark tooling
	cd tools/phpbench && composer install

install-static-analysis-dependencies: install-root-dependencies install-benchmark-dependencies ## install dependencies for static analysis tooling
	cd tools/psalm && composer install

install-unit-tests-dependencies: install-root-dependencies                       ## install dependencies for the test suite
	cd tools/phpunit && composer install

install: install-root-dependencies install-coding-standard-dependencies install-benchmark-dependencies install-static-analysis-dependencies install-unit-tests-dependencies ## install all dependencies for a development environment

coding-standard-fix:                                                            ## apply automated coding standard fixes
	PHP_CS_FIXER_IGNORE_ENV=1 ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=tools/php-cs-fixer/.php_cs.dist.php
	./tools/php-codesniffer/vendor/bin/phpcbf --basepath=. --standard=tools/php-codesniffer/.phpcs.xml

coding-standard-check:                                                          ## check coding-standard compliance
	PHP_CS_FIXER_IGNORE_ENV=1 ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=tools/php-cs-fixer/.php_cs.dist.php --dry-run
	./tools/php-codesniffer/vendor/bin/phpcs --basepath=. --standard=tools/php-codesniffer/.phpcs.xml

benchmarks:                                                                     ## run benchmarks
	./tools/phpbench/vendor/bin/phpbench run --config tools/phpbench/phpbench.json

create-benchmark-reference:                                                     ## run benchmarks, mark current run as "reference"
	./tools/phpbench/vendor/bin/phpbench run --config tools/phpbench/phpbench.json --tag=benchmark_reference

compare-benchmark-to-reference:                                                 ## run benchmarks, compare result to the "reference" run
	./tools/phpbench/vendor/bin/phpbench run --config tools/phpbench/phpbench.json --ref=benchmark_reference

static-analysis:                                                                ## run static analysis checks
	./tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml --show-info=true --no-cache
	./tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml tests/static-analysis --no-cache

type-coverage:                                                                  ## send static analysis type coverage metrics to https://shepherd.dev/
	./tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml --shepherd --stats

security-analysis:                                                              ## run static analysis security checks
	./tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml --taint-analysis

unit-tests:                                                                     ## run unit test suite
	./tools/phpunit/vendor/bin/phpunit -c tools/phpunit/phpunit.xml.dist

code-coverage: unit-tests                                                       ## generate and upload test coverage metrics to https://coveralls.io/
	composer global require php-coveralls/php-coveralls
	php-coveralls -x tests/logs/clover.xml -o tests/logs/coveralls-upload.json -v

docs-generate:                                                                  ## regenerate docs
	php docs/documenter.php

docs-check:                                                                     ## checks if docs are up to date
	php docs/documenter.php check

check: coding-standard-check static-analysis security-analysis unit-tests docs-check  ## run quick checks for local development iterations
