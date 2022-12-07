<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Test;

use PHPUnit\Framework\TestCase;
use Sebk\SmallClassManipulator\Configuration\Bean\SelectorConfiguration;
use Sebk\SmallClassManipulator\Configuration\Configuration;

class ConfigurationTest extends TestCase
{

    const CONFIG = [
        'root_dir' => __DIR__ . '/data',
        'selectors' => [
            'test' => [
                '\DataTest\Testing' => 'DataTest',
                '\Empty' => 'Empty',
            ]
        ],
    ];

    public function testConfigCreation(): Configuration
    {
        return new Configuration(static::CONFIG);
    }

}