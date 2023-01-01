<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace DataTest\Testing;


use DataTest\Testing\Contracts\TestInterface;

/**
 * Class TestClass
 */
final class TestFinalClass implements TestInterface
{

    /** @var string CONST */
    const CONST = "const";
    // PUBLIC CONST
    public const PUBLIC_CONST = "const public";
    /**
     * PRIVATE CONST
     */
    private const PRIVATE_CONST = "const private";
    /*PROTECTED CONST*/
    protected const PROTECTED_CONST = "const protected";

    /**
     * @var string $publicProp
     */
    public string $publicProp = "public";
    // private
    /* prop */
    private $privateProp = "private";
    /** @var string $protectedProp */
    protected int $protectedProp = 0;

    /** @var string $publicStaticProp */
    static public $publicStaticProp = "public static";
    /** @var string  */
    static private string $privateStaticProp = "private static";
    /** @var float $protectedStaticProp */
    static protected float $protectedStaticProp = 0.1;

    public function __construct(
        public string $publicConstructor,
        private int $privateConstructor,
        protected float $protectedConstructor,
        string $parameter
    )
    {
        echo "Constructor";
    }

    /**
     * Test
     * @param string $param
     * @param $param2
     * @param ...$otherParams
     * @return string
     */
    public function publicFunc(string $param, $param2, ...$otherParams): string
    {
        return "public func";
    }

    // Test2
    private function privateFunc(string $param, $param2, ...$otherParams): mixed
    {
        return "}private func";
    }

    // Test3
    protected function protectedFunc(string $param, $param2, int ...$otherParams): int
    {
        return $otherParams[0];
    }

    public static function publicStaticFunc(string $param, $param2, ...$otherParams): string
    {
        return "public func";
    }

    // Test5
    private static function privateStaticFunc(string $param, $param2, ...$otherParams): mixed
    {
        return "}private func";
    }

    // Test6
    protected static function protectedStaticFunc(string $param, $param2, int ...$otherParams): int
    {
        return $otherParams[0];
    }

    // End of class
}