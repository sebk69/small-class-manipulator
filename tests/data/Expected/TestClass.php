<?php

/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Empty\Testing;


/**
 * Class TestClass
 */
class TestClass
{


    /** @var string CONST */
    public const CONST = "const";

    /* PUBLIC CONST*/
    public const PUBLIC_CONST = "const public"; // public test

    /**
     * PRIVATE CONST
     */
    private const PRIVATE_CONST = "const private";
    protected const PROTECTED_CONST = "const protected";

    /**
     * @var string $publicProp
     */
    protected string $publicProp = "public";

    /* private
 prop */
    protected mixed $privateProp = "private";
    protected int $protectedProp = 0;

    /** @var string $publicStaticProp */
    protected static mixed $publicStaticProp = "public static";

    /** @var string  */
    protected static string $privateStaticProp = "private static";

    /** @var float $protectedStaticProp */
    protected static float $protectedStaticProp = 0.1;

    public function __construct (
        public string $publicConstructor,
        private int $privateConstructor,
        protected float $protectedConstructor,
        string $parameter
    ) {
        echo "Constructor";
    }

    /**
     * Test
     * @param string $param
     * @param $param2
     * @param ...$otherParams
     * @return string
     */
    public function publicFunc (
        string $param,
        mixed $param2,
        mixed ...$otherParams
    ): string {
        return "public func";
    }

    /* Test2*/
    private function privateFunc (
        string $param,
        mixed $param2,
        mixed ...$otherParams
    ): mixed {
        return "}private func";
    }

    /* Test3*/
    protected function protectedFunc (
        string $param,
        mixed $param2,
        int ...$otherParams
    ): int {
        return $otherParams[0];
    }

    public static function publicStaticFunc (
        string $param,
        mixed $param2,
        mixed ...$otherParams
    ): string {
        return "public func";
    }

    /* Test5*/
    private static function privateStaticFunc (
        string $param,
        mixed $param2,
        mixed ...$otherParams
    ): mixed {
        return "}private func";
    }

    /* Test6*/
    protected static function protectedStaticFunc (
        string $param,
        mixed $param2,
        int ...$otherParams
    ): int {
        return $otherParams[0];
    }

}