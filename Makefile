ifndef u
u:=sotatek
endif

ifndef env
env:=dev
endif

OS:=$(shell uname)

init-app:
	cp .env.example .env
	composer install
	php artisan key:generate
	php artisan jwt:secret
	php artisan migrate
	php artisan db:seed
	php artisan storage:link
	npm install && npm run dev

build:
	npm run dev

watch:
	npm run watch

autoload:
	composer dump-autoload

route:
	php artisan route:list
