test:
	docker run --rm -it -v $(PWD):/app -w /app/tests php:8.0-cli ../vendor/bin/phpunit

stan:
	docker run --rm -it -v $(PWD):/app -w /app php:8.0-cli vendor/bin/phpstan analyse