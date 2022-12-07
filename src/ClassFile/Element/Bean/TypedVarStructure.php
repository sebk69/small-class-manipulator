<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Bean;

use Sebk\SmallClassManipulator\ClassFile\Element\Trait\ClassScopes;

class TypedVarStructure
{

    public function __construct(
        protected string $name,
        protected string $type = 'mixed',
        protected ClassScopes|null $scope = null
    ) {}

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return TypedVarStructure
     */
    public function setType(string $type): TypedVarStructure
    {
        $this->type = $type;
        return $this;
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
     * @return TypedVarStructure
     */
    public function setName(string $name): TypedVarStructure
    {
        $this->name = $name;
        return $this;
    }

}