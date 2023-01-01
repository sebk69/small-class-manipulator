<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Test\ClassFile;

use DataTest\Testing\Contracts\TestInterface;
use DataTest\Testing\Contracts\TestInterface2;
use PHPUnit\Framework\TestCase;
use DataTest\Testing\TestAbstractClass;
use DataTest\Testing\TestClass;
use DataTest\Testing\TestExtends;
use DataTest\Testing\TestFinalClass;
use Sebk\SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use Sebk\SmallClassManipulator\Configuration\Configuration;
use Sebk\SmallClassManipulator\ClassManipulator;

class ClassFileGenerateTest extends TestCase
{

    const CONFIG = [
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
    ];

    protected Configuration $configuration;
    protected ClassManipulator $classManipulator;


    public function setUp(): void
    {

        $this->configuration = new Configuration(static::CONFIG);
        $this->classManipulator = new ClassManipulator(static::CONFIG);

        parent::setUp();

    }

    public function testConfiguration()
    {

        self::assertFileExists($this->configuration->getSelector('test')['testing']->getClassFilepath(TestAbstractClass::class));
        self::assertFileExists($this->configuration->getSelector('test')['testing']->getClassFilepath(TestClass::class));
        self::assertFileExists($this->configuration->getSelector('test')['testing']->getClassFilepath(TestExtends::class));
        self::assertFileExists($this->configuration->getSelector('test')['testing']->getClassFilepath(TestFinalClass::class));

    }

    public function testSimpleClass()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestClass::class);

        // Change namespace
        $classFile->getNamespace()->setElement(str_replace('DataTest', 'Empty', $classFile->getNamespace()->getElement()));

        // Write file
        $classFile->generate($this->classManipulator->getClassFullPath('test', $classFile));

        self::assertEquals(
            file_get_contents(__DIR__ . '/../data/Expected/TestClass.php'),
            file_get_contents($this->classManipulator->getClassFullPath('test', $classFile))
        );

    }

    public function testAbstractClass()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestAbstractClass::class);

        // Change namespace
        $classFile->getNamespace()->setElement(str_replace('DataTest', 'Empty', $classFile->getNamespace()->getElement()));

        // Write file
        $classFile->generate($this->classManipulator->getClassFullPath('test', $classFile));

        self::assertEquals(
            file_get_contents(__DIR__ . '/../data/Expected/TestAbstractClass.php'),
            file_get_contents($this->classManipulator->getClassFullPath('test', $classFile))
        );
    }
    public function testClassExtends()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestExtends::class);

        // Change namespace
        $classFile->getNamespace()->setElement(str_replace('DataTest', 'Empty', $classFile->getNamespace()->getElement()));

        // Write file
        $classFile->generate($this->classManipulator->getClassFullPath('test', $classFile));

        self::assertEquals(
            file_get_contents(__DIR__ . '/../data/Expected/TestExtends.php'),
            file_get_contents($this->classManipulator->getClassFullPath('test', $classFile))
        );
    }

    public function testClassFinal()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestFinalClass::class);

        // Change namespace
        $classFile->getNamespace()->setElement(str_replace('DataTest', 'Empty', $classFile->getNamespace()->getElement()));

        // Write file
        $classFile->generate($this->classManipulator->getClassFullPath('test', $classFile));

        self::assertEquals(
            file_get_contents(__DIR__ . '/../data/Expected/TestFinalClass.php'),
            file_get_contents($this->classManipulator->getClassFullPath('test', $classFile))
        );
    }

}