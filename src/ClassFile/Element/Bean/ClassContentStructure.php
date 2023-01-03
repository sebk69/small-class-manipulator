<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element\Bean;

use SmallClassManipulator\ClassFile\Element\ConstElement;
use SmallClassManipulator\ClassFile\Element\MethodElement;
use SmallClassManipulator\ClassFile\Element\PropertyElement;
use SmallClassManipulator\ClassFile\Element\TraitElement;
use SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class ClassContentStructure
{

    /** @var TraitElement[] */
    protected array $traits = [];
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
     * @return array
     */
    public function getTraits(): array
    {
        return $this->traits;
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
     * Add a trait use element
     * @param TraitElement $element
     * @return $this
     */
    public function addTrait(TraitElement $element): ClassContentStructure
    {
        $this->traits[$element->getElement()] = $element;

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