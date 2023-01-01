<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element\Bean;

use Sebk\SmallClassManipulator\ClassFile\Element\ConstElement;
use Sebk\SmallClassManipulator\ClassFile\Element\MethodElement;
use Sebk\SmallClassManipulator\ClassFile\Element\PropertyElement;
use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class ClassContentStructure
{

    /** @var MethodElement[] */
    protected array $methods = [];

    /** @var ConstElement[] */
    protected array $consts = [];

    /** @var PropertyElement[] */
    protected array $properties = [];

    /**
     * @return MethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return PropertyElement[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return ConstElement[]
     */
    public function getConsts(): array
    {
        return $this->consts;
    }

    /**
     * Add a method element
     * @param MethodElement $element
     * @return $this
     * @throws SyntaxErrorException
     */
    public function addMethod(MethodElement $element): ClassContentStructure
    {
        if (array_key_exists($element->getElement()->getName(), $this->methods)) {
            throw new SyntaxErrorException('Duplicate method : ' . $element->getElement()->getName());
        }

        $this->methods[$element->getElement()->getName()] = $element;

        return $this;
    }

    /**
     * Add a const element
     * @param ConstElement $element
     * @return $this
     * @throws SyntaxErrorException
     */
    public function addConst(ConstElement $element): ClassContentStructure
    {
        if (array_key_exists($element->getElement()->getName(), $this->consts)) {
            throw new SyntaxErrorException('Duplicate const : ' . $element->getElement()['name']);
        }

        $this->consts[$element->getElement()->getName()] = $element;

        return $this;
    }

    /**
     * Add a property element
     * @param PropertyElement $element
     * @return $this
     * @throws SyntaxErrorException
     */
    public function addProperty(PropertyElement $element): ClassContentStructure
    {
        if (array_key_exists($element->getElement()->getName(), $this->properties)) {
            throw new SyntaxErrorException('Duplicate property : ' . $element->getElement()->getName());
        }

        $this->properties[$element->getElement()->getName()] = $element;

        return $this;
    }

}