.PHONY: clean install export test test-php test-js

node_modules:
	npm install

vendor:
	composer install

install: node_modules vendor

wp-opening-hours.zip: install
	zip -r wp-opening-hours.zip classes dist includes language views functions.php \
	LICENSE readme.txt run.php wp-opening-hours.php

build: node_modules
	NODE_ENV=production ./node_modules/.bin/webpack

test-php: vendor
	./vendor/bin/phpunit

test-js: node_modules
	./node_modules/.bin/jest

test: test-php test-js

export: clean build test wp-opening-hours.zip
	echo "Exported to wp-opening-hours.zip"

clean:
	rm -rf node_modules vendor dist

prettify:
	pwd && ./node_modules/.bin/prettier --parser php --write classes/**/*.php ./*.php tests/**/*.php
