<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Enum;

use Sebk\SmallClassManipulator\ClassFile\Element\Exception\ClassScopeException;

enum ClassScope {
    case public;
    case private;
    case protected;

    /**
     * @return array
     */
    public static function listCasesAsString(): array
    {
        return array_map(function (self $element) {
            return $element->name;
        }, ClassScope::cases());
    }

    /**
     * @param string $scope
     * @return ClassScope
     * @throws ClassScopeException
     * @throws ClassScopeException
     */
    public static function getScopeFromString(string $scope): ClassScope
    {
        foreach (ClassScope::cases() as $case) {
            if ($scope == $case->name) {
                return $case;
            }
        }

        throw new ClassScopeException('Undefined scope ' . $scope);
    }
}