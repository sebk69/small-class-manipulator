<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace DataTest\Testing;

use DataTest\Testing\Contracts\TestInterface;
use DataTest\Testing\Contracts\TestInterface2;

class TestExtends extends TestAbstractClass implements TestInterface, TestInterface2
{

    public function __construct() {}

}