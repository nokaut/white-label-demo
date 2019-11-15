all: build

build:
	docker-compose build
	docker-compose run app install

up:
	docker-compose up

down:
	docker-compose down
