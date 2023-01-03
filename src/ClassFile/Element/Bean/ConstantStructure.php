<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element\Bean;

use SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use SmallClassManipulator\ClassFile\Element\Trait\ClassScoped;

class ConstantStructure
{

    use ClassScoped;

    public function __construct(
        protected string $name,
        protected string $value,
        ClassScope|null $scope = ClassScope::public,
    ) {
        $this->setScope($scope);
        $this->setStatic(true);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ConstantStructure
     */
    public function setName(string $name): ConstantStructure
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ConstantStructure
     */
    public function setValue(string $value): ConstantStructure
    {
        $this->value = $value;
        return $this;
    }

}