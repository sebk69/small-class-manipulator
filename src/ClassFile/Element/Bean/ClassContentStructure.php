<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Bean;

use Sebk\SmallClassManipulator\ClassFile\Element\MethodElement;
use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class ClassContentStructure
{

    /**
     * @var MethodElement[]
     */
    protected array $methods = [];

    /**
     * @var Property
     */
    protected array $properties = [];

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return ClassContentStructure
     */
    public function setMethods(array $methods): ClassContentStructure
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return ClassContentStructure
     */
    public function setProperties(array $properties): ClassContentStructure
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Add a method
     * @param MethodElement $element
     * @return $this
     * @throws SyntaxErrorException
     */
    public function addMethod(MethodElement $element): ClassContentStructure
    {
        if (array_key_exists($element->getName(), $this->methods)) {
            throw new SyntaxErrorException('Duplicate method : ' . $element->getName());
        }

        $this->methods[$element->getName()] = $element;

        return $this;
    }
}