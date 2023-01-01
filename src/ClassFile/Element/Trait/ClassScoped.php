<?php

/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Trait;

use Sebk\SmallClassManipulator\ClassFile\Element\Enum\ClassScope;

trait ClassScoped
{

    protected bool $static = false;
    protected ClassScope|null $scope;

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
    public function getScope(): ClassScope|null
    {
        return $this->scope;
    }

    /**
     * @param ClassScope $scope
     * @return ClassScoped
     */
    public function setScope(ClassScope|null $scope): static
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