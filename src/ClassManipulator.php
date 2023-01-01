<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator;

use Sebk\SmallClassManipulator\ClassFile\ClassFile;
use Sebk\SmallClassManipulator\Configuration\Configuration;

class ClassManipulator
{

    protected Configuration $configuration;

    public function __construct(array $configArray)
    {
        $this->configuration = new Configuration($configArray);
    }

    public function getClass(string $selector, string $fullClassname): ClassFile
    {
        return (new ClassFile())
            ->fromFile($this->configuration->getSelector($selector)->getClassFilepath($fullClassname));
    }

    public function getClassFullPath(string $selector, ClassFile $class): string
    {
        return $this
            ->configuration
            ->getSelector($selector)
            ->getClassFilepath($class->getNamespace()->getElement() . '\\' . $class->getClassname()->getElement());
    }

}