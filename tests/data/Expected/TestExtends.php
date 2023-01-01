<?php

/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Empty\Testing; // test line comment

/* test */
use DataTest\Testing\Contracts\TestInterface; // test2
use DataTest\Testing\Contracts\TestInterface2;

/**
 * class TestExtends
 */
class TestExtends extends TestAbstractClass implements TestInterface, TestInterface2 // test line comment
{


    public function __construct () // construct command
    {}

}