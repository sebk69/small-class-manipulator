# small-class-manipulator

This lib allow you to read and parse a php file containing class into a php object.

You can then easily add or remove components and rewrite file with modifications.

## Unit test

To run unit tests, you are required to install docker and docker-compose :
```bash
$ apt-get install docker docker-compose
```

Then go to root of lib and run :
```bash
$ docker-compose up -d --build
```

If the tests fail the command will return an error.

In development, you can set env var "BUILD" in docker-compose.yml, then the container will not stop, allow you to run tests using :
```bash
$ docker exec -it small-class-manipulator-unit-test ./vendor/bin/phpunit --testdox tests
```