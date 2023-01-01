<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Bean;

use Sebk\SmallClassManipulator\ClassFile\Element\Trait\ClassScoped;
use Sebk\SmallClassManipulator\ClassFile\Element\Enum\ClassScope;

class TypedVarStructure
{

    use ClassScoped;

    public function __construct(
        protected string $name,
        protected string|null $type = 'mixed',
        protected string|null $value = null,
        ClassScope|null $scope = null,
        bool $isStatic = false,
    ) {
        if ($this->type === null) {
            $this->type = 'mixed';
        }
        $this->setStatic($isStatic);

        $this->setScope($scope);
    }

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

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return TypedVarStructure
     */
    public function setValue(?string $value): TypedVarStructure
    {
        $this->value = $value;
        return $this;
    }

}