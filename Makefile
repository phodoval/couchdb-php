test:
	docker run --rm -it -v $(PWD):/app -w /app/tests php:8.1-cli ../vendor/bin/phpunit

stan:
	docker run --rm -it -v $(PWD):/app -w /app php:8.1-cli vendor/bin/phpstan analyse

install:
	docker run --rm -it -v $(PWD):/app -w /app composer:2.4 composer install

update:
	docker run --rm -it -v $(PWD):/app -w /app composer:2.4 composer update