install-root-dependencies:
	composer update

install-coding-standard-dependencies:
	cd tools/php-cs-fixer && composer update --ignore-platform-req php
	cd tools/php-codesniffer && composer update

install-static-analysis-dependencies:
	cd tools/psalm && composer update

install-unit-tests-dependencies:
	cd tools/phpunit && composer update

install: install-root-dependencies install-coding-standard-dependencies install-static-analysis-dependencies install-unit-tests-dependencies

coding-standard-fix:
	php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=tools/php-cs-fixer/.php_cs.dist
	php tools/php-codesniffer/vendor/bin/phpcbf --basepath=. --standard=tools/php-codesniffer/.phpcs.xml

coding-standard-check:
	php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=tools/php-cs-fixer/.php_cs.dist --dry-run
	php tools/php-codesniffer/vendor/bin/phpcs --basepath=. --standard=tools/php-codesniffer/.phpcs.xml

static-analysis:
	php tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml
	php tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml tests/static-analysis

type-coverage:
	php tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml --shepherd --stats

security-analysis:
	php tools/psalm/vendor/bin/psalm -c tools/psalm/psalm.xml --taint-analysis

unit-tests:
	php tools/phpunit/vendor/bin/phpunit -c tools/phpunit/phpunit.xml.dist

code-coverage: unit-tests
	composer global require php-coveralls/php-coveralls
	php-coveralls -x tests/logs/clover.xml -o tests/logs/coveralls-upload.json -v

check: coding-standard-check static-analysis security-analysis unit-tests
