<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Trait;

use Sebk\SmallClassManipulator\ClassFile\Element\Trait\Exception\ClassScopeException;

enum ClassScopes {
    case public;
    case private;
    case protected;

    public static function listCasesAsString()
    {
        return array_map(function (ClassScopes $element) {
            return $element->name;
        }, static::cases());
    }

    /**
     * @param string $scope
     * @return ClassScopes
     */
    public static function getScopeFromString(string $scope): ClassScopes
    {
        foreach (ClassScopes::cases() as $case) {
            if ($scope == $case->name) {
                return $case;
            }
        }

        throw new ClassScopeException('Undefined scope ' . $scope);
    }
}

trait ClassScoped
{

    protected bool $static = false;
    protected ClassScopes $scope;

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * @param bool $static
     * @return ClassScoped
     */
    public function setStatic(bool $static): static
    {
        $this->static = $static;

        return $this;
    }

    /**
     * @return ClassScope
     */
    public function getScope(): ClassScope
    {
        return $this->scope;
    }

    /**
     * @param ClassScope $scope
     * @return ClassScopes
     */
    public function setScope(ClassScopes $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScopeString(): string
    {
        return ($this->static ? 'static ' : '') . $this->scope->name . ' ';
    }

}