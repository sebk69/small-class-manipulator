<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\Test\ClassFile;

use DataTest\Testing\Contracts\TestInterface;
use DataTest\Testing\Contracts\TestInterface2;
use PHPUnit\Framework\TestCase;
use DataTest\Testing\TestAbstractClass;
use DataTest\Testing\TestClass;
use DataTest\Testing\TestExtends;
use DataTest\Testing\TestFinalClass;
use SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use SmallClassManipulator\Configuration\Configuration;
use SmallClassManipulator\ClassManipulator;

class ClassFileParseTest extends TestCase
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

    public function testClassExtends()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestExtends::class);

        // Namespace
        self::assertEquals('DataTest\\Testing', $classFile->getNamespace()->getElement());
        self::assertEquals('
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 
', $classFile->getNamespace()->getCommentBefore());

        // uses
        self::assertEquals(TestInterface::class, $classFile->getUses()[0]->getElement());
        self::assertEquals(TestInterface2::class, $classFile->getUses()[1]->getElement());

        // Class
        self::assertEquals('TestExtends', $classFile->getClassname()->getElement());
        self::assertEquals('TestAbstractClass', $classFile->getExtends()->getElement());
        self::assertEquals('TestInterface', $classFile->getImplements()[0]);
        self::assertEquals('TestInterface2', $classFile->getImplements()[1]);
        self::assertFalse($classFile->isAbstract());
        self::assertFalse($classFile->isFinal());
        self::assertEquals("*\n * class TestExtends\n \n", $classFile->getClassname()->getCommentBefore());
        self::assertEquals(" Class line comment\n", $classFile->getClassname()->getLineComment());
    }

    public function testSimpleClass()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestClass::class);

        // Namespace
        self::assertEquals('DataTest\\Testing', $classFile->getNamespace()->getElement());
        self::assertEquals('
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 
', $classFile->getNamespace()->getCommentBefore());

        // uses
        self::assertEquals(0, count($classFile->getUses()));

        // Class
        self::assertEquals('TestClass', $classFile->getClassname()->getElement());
        self::assertEmpty($classFile->getExtends());
        self::assertEquals(0, count($classFile->getImplements()));
        self::assertFalse($classFile->isAbstract());
        self::assertFalse($classFile->isFinal());
        self::assertEquals('*
 * Class TestClass
 
', $classFile->getClassname()->getCommentBefore());

        // Consts
        $consts = $classFile->getContentStructure()->getConsts();

        self::assertArrayHasKey('CONST', $consts);
        self::assertEquals("* @var string CONST \n", $consts['CONST']->getCommentBefore());
        self::assertEquals('CONST', $consts['CONST']->getElement()->getName());
        self::assertEquals('"const"', $consts['CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['CONST']->getElement()->getScope());
        self::assertTrue($consts['CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PUBLIC_CONST', $consts);
        self::assertEquals(" PUBLIC CONST\n", $consts['PUBLIC_CONST']->getCommentBefore());
        self::assertEquals('PUBLIC_CONST', $consts['PUBLIC_CONST']->getElement()->getName());
        self::assertEquals('"const public"', $consts['PUBLIC_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['PUBLIC_CONST']->getElement()->getScope());
        self::assertTrue($consts['PUBLIC_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PRIVATE_CONST', $consts);
        self::assertEquals("*\n     * PRIVATE CONST\n     \n", $consts['PRIVATE_CONST']->getCommentBefore());
        self::assertEquals('PRIVATE_CONST', $consts['PRIVATE_CONST']->getElement()->getName());
        self::assertEquals('"const private"', $consts['PRIVATE_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::private, $consts['PRIVATE_CONST']->getElement()->getScope());
        self::assertTrue($consts['PRIVATE_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PROTECTED_CONST', $consts);
        self::assertEquals("", $consts['PROTECTED_CONST']->getCommentBefore());
        self::assertEquals('PROTECTED_CONST', $consts['PROTECTED_CONST']->getElement()->getName());
        self::assertEquals('"const protected"', $consts['PROTECTED_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::protected, $consts['PROTECTED_CONST']->getElement()->getScope());
        self::assertTrue($consts['PROTECTED_CONST']->getElement()->isStatic());

        // properties
        $properties = $classFile->getContentStructure()->getProperties();

        self::assertArrayHasKey('$publicProp', $properties);
        self::assertEquals("*\n     * @var string \$publicProp\n     \n", $properties['$publicProp']->getCommentBefore());
        self::assertEquals('$publicProp', $properties['$publicProp']->getElement()->getName());
        self::assertEquals('string', $properties['$publicProp']->getElement()->getType());
        self::assertEquals('"public"', $properties['$publicProp']->getElement()->getValue());
        self::assertFalse($properties['$publicProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateProp', $properties);
        self::assertEquals(" private\n prop \n", $properties['$privateProp']->getCommentBefore());
        self::assertEquals('$privateProp', $properties['$privateProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$privateProp']->getElement()->getType());
        self::assertEquals('"private"', $properties['$privateProp']->getElement()->getValue());
        self::assertFalse($properties['$privateProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedProp', $properties);
        self::assertEquals("", $properties['$protectedProp']->getCommentBefore());
        self::assertEquals('$protectedProp', $properties['$protectedProp']->getElement()->getName());
        self::assertEquals('int', $properties['$protectedProp']->getElement()->getType());
        self::assertEquals('0', $properties['$protectedProp']->getElement()->getValue());
        self::assertFalse($properties['$protectedProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedProp']->getElement()->getScope());

        self::assertArrayHasKey('$publicStaticProp', $properties);
        self::assertEquals('$publicStaticProp', $properties['$publicStaticProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$publicStaticProp']->getElement()->getType());
        self::assertEquals('"public static"', $properties['$publicStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$publicStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateStaticProp', $properties);
        self::assertEquals("* @var string  \n", $properties['$privateStaticProp']->getCommentBefore());
        self::assertEquals('$privateStaticProp', $properties['$privateStaticProp']->getElement()->getName());
        self::assertEquals('string', $properties['$privateStaticProp']->getElement()->getType());
        self::assertEquals('"private static"', $properties['$privateStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$privateStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedStaticProp', $properties);
        self::assertEquals("* @var float \$protectedStaticProp \n", $properties['$protectedStaticProp']->getCommentBefore());
        self::assertEquals('$protectedStaticProp', $properties['$protectedStaticProp']->getElement()->getName());
        self::assertEquals('float', $properties['$protectedStaticProp']->getElement()->getType());
        self::assertEquals('0.1', $properties['$protectedStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$protectedStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedStaticProp']->getElement()->getScope());

        // Methods
        $methods = $classFile->getContentStructure()->getMethods();

        self::assertArrayHasKey('__construct', $methods);
        self::assertEquals('__construct', $methods['__construct']->getElement()->getName());
        self::assertEquals(ClassScope::public, $methods['__construct']->getElement()->getScope());
        self::assertFalse($methods['__construct']->getElement()->isStatic());
        $parameters = $methods['__construct']->getElement()->getParameters();
        self::assertArrayHasKey('$publicConstructor', $parameters);
        self::assertEquals('$publicConstructor', $parameters['$publicConstructor']->getName());
        self::assertEquals('string', $parameters['$publicConstructor']->getType());
        self::assertFalse($parameters['$publicConstructor']->isStatic());
        self::assertEquals(ClassScope::public, $parameters['$publicConstructor']->getScope());
        self::assertArrayHasKey('$privateConstructor', $parameters);
        self::assertEquals('$privateConstructor', $parameters['$privateConstructor']->getName());
        self::assertEquals('int', $parameters['$privateConstructor']->getType());
        self::assertFalse($parameters['$privateConstructor']->isStatic());
        self::assertEquals(ClassScope::private, $parameters['$privateConstructor']->getScope());
        self::assertArrayHasKey('$protectedConstructor', $parameters);
        self::assertEquals('$protectedConstructor', $parameters['$protectedConstructor']->getName());
        self::assertEquals('float', $parameters['$protectedConstructor']->getType());
        self::assertFalse($parameters['$protectedConstructor']->isStatic());
        self::assertEquals(ClassScope::protected, $parameters['$protectedConstructor']->getScope());
        self::assertArrayHasKey('$parameter', $parameters);
        self::assertEquals('$parameter', $parameters['$parameter']->getName());
        self::assertEquals('string', $parameters['$parameter']->getType());
        self::assertFalse($parameters['$parameter']->isStatic());
        self::assertEquals(null, $parameters['$parameter']->getScope());
        self::assertEquals('{
        echo "Constructor";
    }', $methods['__construct']->getElement()->getContent());
        self::assertEmpty($methods['__construct']->getElement()->getReturnType());

        self::assertArrayHasKey('publicFunc', $methods);
        self::assertEquals('publicFunc', $methods['publicFunc']->getElement()->getName());
        self::assertEquals('*
     * Test
     * @param string $param
     * @param $param2
     * @param ...$otherParams
     * @return string
     
', $methods['publicFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicFunc']->getElement()->getScope());
        self::assertFalse($methods['publicFunc']->getElement()->isStatic());
        $parameters = $methods['publicFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateFunc', $methods);
        self::assertEquals('privateFunc', $methods['privateFunc']->getElement()->getName());
        self::assertEquals(' Test2
', $methods['privateFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateFunc']->getElement()->getScope());
        self::assertFalse($methods['privateFunc']->getElement()->isStatic());
        $parameters = $methods['privateFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedFunc', $methods);
        self::assertEquals('protectedFunc', $methods['protectedFunc']->getElement()->getName());
        self::assertEquals(' Test3
', $methods['protectedFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedFunc']->getElement()->getScope());
        self::assertFalse($methods['protectedFunc']->getElement()->isStatic());
        $parameters = $methods['protectedFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('publicStaticFunc', $methods);
        self::assertEquals('publicStaticFunc', $methods['publicStaticFunc']->getElement()->getName());
        self::assertEquals('', $methods['publicStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['publicStaticFunc']->getElement()->isStatic());
        $parameters = $methods['publicStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicStaticFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedStaticFunc', $methods);
        self::assertEquals('protectedStaticFunc', $methods['protectedStaticFunc']->getElement()->getName());
        self::assertEquals(' Test6
', $methods['protectedStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['protectedStaticFunc']->getElement()->isStatic());
        $parameters = $methods['protectedStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedStaticFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateStaticFunc', $methods);
        self::assertEquals('privateStaticFunc', $methods['privateStaticFunc']->getElement()->getName());
        self::assertEquals(' Test5
', $methods['privateStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['privateStaticFunc']->getElement()->isStatic());
        $parameters = $methods['privateStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateStaticFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateStaticFunc']->getElement()->getReturnType());

    }

    public function testFinalClass()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestFinalClass::class);

        // Namespace
        self::assertEquals('DataTest\\Testing', $classFile->getNamespace()->getElement());
        self::assertEquals('
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 
', $classFile->getNamespace()->getCommentBefore());

        // uses
        self::assertEquals(TestInterface::class, $classFile->getUses()[0]->getElement());

        // Class
        self::assertEquals('TestFinalClass', $classFile->getClassname()->getElement());
        self::assertEquals('TestInterface', $classFile->getImplements()[0]);
        self::assertFalse($classFile->isAbstract());
        self::assertTrue($classFile->isFinal());
        self::assertEquals('*
 * Class TestClass
 
', $classFile->getClassname()->getCommentBefore());

        // Consts
        $consts = $classFile->getContentStructure()->getConsts();

        self::assertArrayHasKey('CONST', $consts);
        self::assertEquals("* @var string CONST \n", $consts['CONST']->getCommentBefore());
        self::assertEquals('CONST', $consts['CONST']->getElement()->getName());
        self::assertEquals('"const"', $consts['CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['CONST']->getElement()->getScope());
        self::assertTrue($consts['CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PUBLIC_CONST', $consts);
        self::assertEquals(" PUBLIC CONST\n", $consts['PUBLIC_CONST']->getCommentBefore());
        self::assertEquals('PUBLIC_CONST', $consts['PUBLIC_CONST']->getElement()->getName());
        self::assertEquals('"const public"', $consts['PUBLIC_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['PUBLIC_CONST']->getElement()->getScope());
        self::assertTrue($consts['PUBLIC_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PRIVATE_CONST', $consts);
        self::assertEquals("*\n     * PRIVATE CONST\n     \n", $consts['PRIVATE_CONST']->getCommentBefore());
        self::assertEquals('PRIVATE_CONST', $consts['PRIVATE_CONST']->getElement()->getName());
        self::assertEquals('"const private"', $consts['PRIVATE_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::private, $consts['PRIVATE_CONST']->getElement()->getScope());
        self::assertTrue($consts['PRIVATE_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PROTECTED_CONST', $consts);
        self::assertEquals("PROTECTED CONST\n", $consts['PROTECTED_CONST']->getCommentBefore());
        self::assertEquals('PROTECTED_CONST', $consts['PROTECTED_CONST']->getElement()->getName());
        self::assertEquals('"const protected"', $consts['PROTECTED_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::protected, $consts['PROTECTED_CONST']->getElement()->getScope());
        self::assertTrue($consts['PROTECTED_CONST']->getElement()->isStatic());

        // properties
        $properties = $classFile->getContentStructure()->getProperties();

        self::assertArrayHasKey('$publicProp', $properties);
        self::assertEquals("*\n     * @var string \$publicProp\n     \n", $properties['$publicProp']->getCommentBefore());
        self::assertEquals('$publicProp', $properties['$publicProp']->getElement()->getName());
        self::assertEquals('string', $properties['$publicProp']->getElement()->getType());
        self::assertEquals('"public"', $properties['$publicProp']->getElement()->getValue());
        self::assertFalse($properties['$publicProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateProp', $properties);
        self::assertEquals(" private\n prop \n", $properties['$privateProp']->getCommentBefore());
        self::assertEquals('$privateProp', $properties['$privateProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$privateProp']->getElement()->getType());
        self::assertEquals('"private"', $properties['$privateProp']->getElement()->getValue());
        self::assertFalse($properties['$privateProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedProp', $properties);
        self::assertEquals("* @var string \$protectedProp \n", $properties['$protectedProp']->getCommentBefore());
        self::assertEquals('$protectedProp', $properties['$protectedProp']->getElement()->getName());
        self::assertEquals('int', $properties['$protectedProp']->getElement()->getType());
        self::assertEquals('0', $properties['$protectedProp']->getElement()->getValue());
        self::assertFalse($properties['$protectedProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedProp']->getElement()->getScope());

        self::assertArrayHasKey('$publicStaticProp', $properties);
        self::assertEquals('$publicStaticProp', $properties['$publicStaticProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$publicStaticProp']->getElement()->getType());
        self::assertEquals('"public static"', $properties['$publicStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$publicStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateStaticProp', $properties);
        self::assertEquals("* @var string  \n", $properties['$privateStaticProp']->getCommentBefore());
        self::assertEquals('$privateStaticProp', $properties['$privateStaticProp']->getElement()->getName());
        self::assertEquals('string', $properties['$privateStaticProp']->getElement()->getType());
        self::assertEquals('"private static"', $properties['$privateStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$privateStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedStaticProp', $properties);
        self::assertEquals("* @var float \$protectedStaticProp \n", $properties['$protectedStaticProp']->getCommentBefore());
        self::assertEquals('$protectedStaticProp', $properties['$protectedStaticProp']->getElement()->getName());
        self::assertEquals('float', $properties['$protectedStaticProp']->getElement()->getType());
        self::assertEquals('0.1', $properties['$protectedStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$protectedStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedStaticProp']->getElement()->getScope());

        // Methods
        $methods = $classFile->getContentStructure()->getMethods();

        self::assertArrayHasKey('__construct', $methods);
        self::assertEquals('__construct', $methods['__construct']->getElement()->getName());
        self::assertEquals(ClassScope::public, $methods['__construct']->getElement()->getScope());
        self::assertFalse($methods['__construct']->getElement()->isStatic());
        $parameters = $methods['__construct']->getElement()->getParameters();
        self::assertArrayHasKey('$publicConstructor', $parameters);
        self::assertEquals('$publicConstructor', $parameters['$publicConstructor']->getName());
        self::assertEquals('string', $parameters['$publicConstructor']->getType());
        self::assertFalse($parameters['$publicConstructor']->isStatic());
        self::assertEquals(ClassScope::public, $parameters['$publicConstructor']->getScope());
        self::assertArrayHasKey('$privateConstructor', $parameters);
        self::assertEquals('$privateConstructor', $parameters['$privateConstructor']->getName());
        self::assertEquals('int', $parameters['$privateConstructor']->getType());
        self::assertFalse($parameters['$privateConstructor']->isStatic());
        self::assertEquals(ClassScope::private, $parameters['$privateConstructor']->getScope());
        self::assertArrayHasKey('$protectedConstructor', $parameters);
        self::assertEquals('$protectedConstructor', $parameters['$protectedConstructor']->getName());
        self::assertEquals('float', $parameters['$protectedConstructor']->getType());
        self::assertFalse($parameters['$protectedConstructor']->isStatic());
        self::assertEquals(ClassScope::protected, $parameters['$protectedConstructor']->getScope());
        self::assertArrayHasKey('$parameter', $parameters);
        self::assertEquals('$parameter', $parameters['$parameter']->getName());
        self::assertEquals('string', $parameters['$parameter']->getType());
        self::assertFalse($parameters['$parameter']->isStatic());
        self::assertEquals(null, $parameters['$parameter']->getScope());
        self::assertEquals('{
        echo "Constructor";
    }', $methods['__construct']->getElement()->getContent());
        self::assertEmpty($methods['__construct']->getElement()->getReturnType());

        self::assertArrayHasKey('publicFunc', $methods);
        self::assertEquals('publicFunc', $methods['publicFunc']->getElement()->getName());
        self::assertEquals('*
     * Test
     * @param string $param
     * @param $param2
     * @param ...$otherParams
     * @return string
     
', $methods['publicFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicFunc']->getElement()->getScope());
        self::assertFalse($methods['publicFunc']->getElement()->isStatic());
        $parameters = $methods['publicFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateFunc', $methods);
        self::assertEquals('privateFunc', $methods['privateFunc']->getElement()->getName());
        self::assertEquals(' Test2
', $methods['privateFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateFunc']->getElement()->getScope());
        self::assertFalse($methods['privateFunc']->getElement()->isStatic());
        $parameters = $methods['privateFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedFunc', $methods);
        self::assertEquals('protectedFunc', $methods['protectedFunc']->getElement()->getName());
        self::assertEquals(' Test3
', $methods['protectedFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedFunc']->getElement()->getScope());
        self::assertFalse($methods['protectedFunc']->getElement()->isStatic());
        $parameters = $methods['protectedFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('publicStaticFunc', $methods);
        self::assertEquals('publicStaticFunc', $methods['publicStaticFunc']->getElement()->getName());
        self::assertEquals('', $methods['publicStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['publicStaticFunc']->getElement()->isStatic());
        $parameters = $methods['publicStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicStaticFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedStaticFunc', $methods);
        self::assertEquals('protectedStaticFunc', $methods['protectedStaticFunc']->getElement()->getName());
        self::assertEquals(' Test6
', $methods['protectedStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['protectedStaticFunc']->getElement()->isStatic());
        $parameters = $methods['protectedStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedStaticFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateStaticFunc', $methods);
        self::assertEquals('privateStaticFunc', $methods['privateStaticFunc']->getElement()->getName());
        self::assertEquals(' Test5
', $methods['privateStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['privateStaticFunc']->getElement()->isStatic());
        $parameters = $methods['privateStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateStaticFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateStaticFunc']->getElement()->getReturnType());

    }

    public function testAbstractClass()
    {

        // Parse class
        $classFile = $this->classManipulator->getClass('test', TestAbstractClass::class);

        // Namespace
        self::assertEquals('DataTest\\Testing', $classFile->getNamespace()->getElement());
        self::assertEquals('
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 
', $classFile->getNamespace()->getCommentBefore());

        // Class
        self::assertEquals('TestAbstractClass', $classFile->getClassname()->getElement());
        self::assertTrue($classFile->isAbstract());
        self::assertFalse($classFile->isFinal());
        self::assertEquals('*
 * Class TestClass
 
', $classFile->getClassname()->getCommentBefore());

        // Consts
        $consts = $classFile->getContentStructure()->getConsts();

        self::assertArrayHasKey('CONST', $consts);
        self::assertEquals("* @var string CONST \n", $consts['CONST']->getCommentBefore());
        self::assertEquals('CONST', $consts['CONST']->getElement()->getName());
        self::assertEquals('"const"', $consts['CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['CONST']->getElement()->getScope());
        self::assertTrue($consts['CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PUBLIC_CONST', $consts);
        self::assertEquals(" PUBLIC CONST\n", $consts['PUBLIC_CONST']->getCommentBefore());
        self::assertEquals('PUBLIC_CONST', $consts['PUBLIC_CONST']->getElement()->getName());
        self::assertEquals('"const public"', $consts['PUBLIC_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::public, $consts['PUBLIC_CONST']->getElement()->getScope());
        self::assertTrue($consts['PUBLIC_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PRIVATE_CONST', $consts);
        self::assertEquals("*\n     * PRIVATE CONST\n     \n", $consts['PRIVATE_CONST']->getCommentBefore());
        self::assertEquals('PRIVATE_CONST', $consts['PRIVATE_CONST']->getElement()->getName());
        self::assertEquals('"const private"', $consts['PRIVATE_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::private, $consts['PRIVATE_CONST']->getElement()->getScope());
        self::assertTrue($consts['PRIVATE_CONST']->getElement()->isStatic());

        self::assertArrayHasKey('PROTECTED_CONST', $consts);
        self::assertEquals("PROTECTED CONST\n", $consts['PROTECTED_CONST']->getCommentBefore());
        self::assertEquals(" protect yourself\n", $consts['PROTECTED_CONST']->getLineComment());
        self::assertEquals('PROTECTED_CONST', $consts['PROTECTED_CONST']->getElement()->getName());
        self::assertEquals('"const protected"', $consts['PROTECTED_CONST']->getElement()->getValue());
        self::assertEquals(ClassScope::protected, $consts['PROTECTED_CONST']->getElement()->getScope());
        self::assertTrue($consts['PROTECTED_CONST']->getElement()->isStatic());

        // properties
        $properties = $classFile->getContentStructure()->getProperties();

        self::assertArrayHasKey('$publicProp', $properties);
        self::assertEquals("*\n     * @var string \$publicProp\n     \n", $properties['$publicProp']->getCommentBefore());
        self::assertEquals('$publicProp', $properties['$publicProp']->getElement()->getName());
        self::assertEquals('string', $properties['$publicProp']->getElement()->getType());
        self::assertEquals('"public"', $properties['$publicProp']->getElement()->getValue());
        self::assertFalse($properties['$publicProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateProp', $properties);
        self::assertEquals(" private\n prop \n", $properties['$privateProp']->getCommentBefore());
        self::assertEquals(" This is very private\n", $properties['$privateProp']->getLineComment());
        self::assertEquals('$privateProp', $properties['$privateProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$privateProp']->getElement()->getType());
        self::assertEquals('"private"', $properties['$privateProp']->getElement()->getValue());
        self::assertFalse($properties['$privateProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedProp', $properties);
        self::assertEquals("* @var string \$protectedProp \n", $properties['$protectedProp']->getCommentBefore());
        self::assertEquals('$protectedProp', $properties['$protectedProp']->getElement()->getName());
        self::assertEquals('int', $properties['$protectedProp']->getElement()->getType());
        self::assertEquals('0', $properties['$protectedProp']->getElement()->getValue());
        self::assertFalse($properties['$protectedProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedProp']->getElement()->getScope());

        self::assertArrayHasKey('$publicStaticProp', $properties);
        self::assertEquals('$publicStaticProp', $properties['$publicStaticProp']->getElement()->getName());
        self::assertEquals('mixed', $properties['$publicStaticProp']->getElement()->getType());
        self::assertEquals('"public static"', $properties['$publicStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$publicStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::public, $properties['$publicStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$privateStaticProp', $properties);
        self::assertEquals("* @var string  \n", $properties['$privateStaticProp']->getCommentBefore());
        self::assertEquals('$privateStaticProp', $properties['$privateStaticProp']->getElement()->getName());
        self::assertEquals('string', $properties['$privateStaticProp']->getElement()->getType());
        self::assertEquals('"private static"', $properties['$privateStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$privateStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::private, $properties['$privateStaticProp']->getElement()->getScope());

        self::assertArrayHasKey('$protectedStaticProp', $properties);
        self::assertEquals("* @var float \$protectedStaticProp \n", $properties['$protectedStaticProp']->getCommentBefore());
        self::assertEquals('$protectedStaticProp', $properties['$protectedStaticProp']->getElement()->getName());
        self::assertEquals('float', $properties['$protectedStaticProp']->getElement()->getType());
        self::assertEquals('0.1', $properties['$protectedStaticProp']->getElement()->getValue());
        self::assertTrue($properties['$protectedStaticProp']->getElement()->isStatic());
        self::assertEquals(ClassScope::protected, $properties['$protectedStaticProp']->getElement()->getScope());

        // Methods
        $methods = $classFile->getContentStructure()->getMethods();

        self::assertArrayHasKey('__construct', $methods);
        self::assertEquals('__construct', $methods['__construct']->getElement()->getName());
        self::assertEquals(ClassScope::public, $methods['__construct']->getElement()->getScope());
        self::assertFalse($methods['__construct']->getElement()->isStatic());
        $parameters = $methods['__construct']->getElement()->getParameters();
        self::assertArrayHasKey('$publicConstructor', $parameters);
        self::assertEquals('$publicConstructor', $parameters['$publicConstructor']->getName());
        self::assertEquals('string', $parameters['$publicConstructor']->getType());
        self::assertFalse($parameters['$publicConstructor']->isStatic());
        self::assertEquals(ClassScope::public, $parameters['$publicConstructor']->getScope());
        self::assertArrayHasKey('$privateConstructor', $parameters);
        self::assertEquals('$privateConstructor', $parameters['$privateConstructor']->getName());
        self::assertEquals('int', $parameters['$privateConstructor']->getType());
        self::assertFalse($parameters['$privateConstructor']->isStatic());
        self::assertEquals(ClassScope::private, $parameters['$privateConstructor']->getScope());
        self::assertArrayHasKey('$protectedConstructor', $parameters);
        self::assertEquals('$protectedConstructor', $parameters['$protectedConstructor']->getName());
        self::assertEquals('float', $parameters['$protectedConstructor']->getType());
        self::assertFalse($parameters['$protectedConstructor']->isStatic());
        self::assertEquals(ClassScope::protected, $parameters['$protectedConstructor']->getScope());
        self::assertArrayHasKey('$parameter', $parameters);
        self::assertEquals('$parameter', $parameters['$parameter']->getName());
        self::assertEquals('string', $parameters['$parameter']->getType());
        self::assertFalse($parameters['$parameter']->isStatic());
        self::assertEquals(null, $parameters['$parameter']->getScope());
        self::assertEquals('{
        echo "Constructor";
    }', $methods['__construct']->getElement()->getContent());
        self::assertEmpty($methods['__construct']->getElement()->getReturnType());

        self::assertArrayHasKey('publicFunc', $methods);
        self::assertEquals('publicFunc', $methods['publicFunc']->getElement()->getName());
        self::assertEquals('*
     * Test
     * @param string $param
     * @param $param2
     * @param ...$otherParams
     * @return string
     
', $methods['publicFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicFunc']->getElement()->getScope());
        self::assertFalse($methods['publicFunc']->getElement()->isStatic());
        $parameters = $methods['publicFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateFunc', $methods);
        self::assertEquals('privateFunc', $methods['privateFunc']->getElement()->getName());
        self::assertEquals(' Test2
', $methods['privateFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateFunc']->getElement()->getScope());
        self::assertFalse($methods['privateFunc']->getElement()->isStatic());
        $parameters = $methods['privateFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedFunc', $methods);
        self::assertEquals('protectedFunc', $methods['protectedFunc']->getElement()->getName());
        self::assertEquals(' Test3
', $methods['protectedFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedFunc']->getElement()->getScope());
        self::assertFalse($methods['protectedFunc']->getElement()->isStatic());
        $parameters = $methods['protectedFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('publicStaticFunc', $methods);
        self::assertEquals('publicStaticFunc', $methods['publicStaticFunc']->getElement()->getName());
        self::assertEquals('', $methods['publicStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::public, $methods['publicStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['publicStaticFunc']->getElement()->isStatic());
        $parameters = $methods['publicStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "public func";
    }', $methods['publicStaticFunc']->getElement()->getContent());
        self::assertEquals('string', $methods['publicStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('protectedStaticFunc', $methods);
        self::assertEquals('protectedStaticFunc', $methods['protectedStaticFunc']->getElement()->getName());
        self::assertEquals(' Test6
', $methods['protectedStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::protected, $methods['protectedStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['protectedStaticFunc']->getElement()->isStatic());
        $parameters = $methods['protectedStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('int', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return $otherParams[0];
    }', $methods['protectedStaticFunc']->getElement()->getContent());
        self::assertEquals('int', $methods['protectedStaticFunc']->getElement()->getReturnType());

        self::assertArrayHasKey('privateStaticFunc', $methods);
        self::assertEquals('privateStaticFunc', $methods['privateStaticFunc']->getElement()->getName());
        self::assertEquals(' Test5
', $methods['privateStaticFunc']->getCommentBefore());
        self::assertEquals(ClassScope::private, $methods['privateStaticFunc']->getElement()->getScope());
        self::assertTrue($methods['privateStaticFunc']->getElement()->isStatic());
        $parameters = $methods['privateStaticFunc']->getElement()->getParameters();
        self::assertArrayHasKey('$param', $parameters);
        self::assertEquals('$param', $parameters['$param']->getName());
        self::assertEquals('string', $parameters['$param']->getType());
        self::assertEquals(null, $parameters['$param']->getValue());
        self::assertFalse($parameters['$param']->isStatic());
        self::assertEquals(null, $parameters['$param']->getScope());
        self::assertArrayHasKey('$param2', $parameters);
        self::assertEquals('$param2', $parameters['$param2']->getName());
        self::assertEquals('mixed', $parameters['$param2']->getType());
        self::assertFalse($parameters['$param2']->isStatic());
        self::assertEquals(null, $parameters['$param2']->getScope());
        self::assertArrayHasKey('...$otherParams', $parameters);
        self::assertEquals('...$otherParams', $parameters['...$otherParams']->getName());
        self::assertEquals('mixed', $parameters['...$otherParams']->getType());
        self::assertFalse($parameters['...$otherParams']->isStatic());
        self::assertEquals(null, $parameters['...$otherParams']->getScope());
        self::assertEquals('{
        return "}private func";
    }', $methods['privateStaticFunc']->getElement()->getContent());
        self::assertEquals('mixed', $methods['privateStaticFunc']->getElement()->getReturnType());

    }

}