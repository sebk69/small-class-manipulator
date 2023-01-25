# small-class-manipulator

This lib allow you to read and parse a php file containing class into a php object.

You can then easily add or remove components and rewrite file with modifications.

# Migrated

This lib has been migrated to [framagit](https://framagit.org/small/small-projects) project.

A new composer package is available at https://packagist.org/packages/small/class-manipulator

Future commits will be done on framagit.

This repository will be removed in few month.

## Parsing and generating classes

### Manipulator instanciation

To parse a class, you must instanciate ClassManipulator :
```php
$classManipulator = new ClassManipulator([
        'rootDir' => __DIR__ . '/../data',
        'selectors' => [
            'test' => [
                'testing' => [
                    'namespace' => 'DataTest\Testing',
                    'path' => 'DataTest',
                ], 'empty' => [
                    'namespace' => 'Empty',
                    'path' => 'Empty',
                ],
            ]
        ],
    ]);
```

The "rootDir" parameter is your "src" directory.

The selectors allow you to isolate namespaces params by categories. Here we have injecting only a "test" selector.

In the test selector, we have as many of namespaces we want and each namespace must contain a namespace and corresponding directory path.

### Parsing

You can now parse a class :
```php
$classFile = $this->classManipulator->getClass('test', \Empty\Testing\TestClass::class);
```

This will return a ClassFile

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

In development, you can set argument "BUILD" to 0 in docker-compose.yml, then the container will not stop, allow you to run tests using :
```bash
$ bin/test
```