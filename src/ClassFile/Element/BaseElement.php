<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element;

use SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class BaseElement extends AbstractElement
{

    protected $element;

    /**
     * @return mixed
     */
    public function getElement(): mixed
    {
        return $this->element;
    }

    /**
     * @param mixed $element
     * @return BaseElement
     */
    public function setElement($element): static
    {
        $this->element = $element;
        return $this;
    }

}