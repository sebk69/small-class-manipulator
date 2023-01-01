<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Test\Configuration;

use DataTest\Testing\TestClass;
use PHPUnit\Framework\TestCase;
use Sebk\SmallClassManipulator\Configuration\Bean\SelectorConfiguration;
use Sebk\SmallClassManipulator\Configuration\Configuration;

class ConfigurationTest extends TestCase
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

    public function setUp(): void
    {

        $this->configuration = new Configuration(static::CONFIG);

        parent::setUp();

    }

    public function testBasic()
    {

        self::assertEquals(static::CONFIG['rootDir'], $this->configuration->getRootDir());

        self::assertInstanceOf(SelectorConfiguration::class, $this->configuration->getSelector('test'));

    }

    public function testSelectorConfiguration()
    {
        self::assertEquals(self::CONFIG['selectors']['test']['testing']['namespace'], $this->configuration->getSelector('test')['testing']->getNamespace());
        self::assertEquals(self::CONFIG['rootDir'] . '/' . self::CONFIG['selectors']['test']['testing']['path'], $this->configuration->getSelector('test')['testing']->getPath());
        self::assertEquals(self::CONFIG['selectors']['test']['empty']['namespace'], $this->configuration->getSelector('test')['empty']->getNamespace());
        self::assertEquals(self::CONFIG['rootDir'] . '/' . self::CONFIG['selectors']['test']['empty']['path'], $this->configuration->getSelector('test')['empty']->getPath());
    }

    public function testSelectorMethods()
    {
        $file = __DIR__ . '/../data/DataTest/TestClass.php';
        self::assertEquals($file, $this->configuration->getSelector('test')->getClassFilepath(TestClass::class));
    }

}