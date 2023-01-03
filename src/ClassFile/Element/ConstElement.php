<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element;

use SmallClassManipulator\ClassFile\Element\Bean\ConstantStructure;
use SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use SmallClassManipulator\ClassFile\Element\Exception\WrongElementClass;

class ConstElement extends AbstractElement
{

    const REGEXP = '^[ \t\n\r]*(public|private|protected)?[ \t\n\r]*const[ \t\n\r]*([a-z|A-Z|0-9|_]*)[ \t\n\r]*=[ \t\n\r]*([\S\s]*)[ \t\n\r]*;';

    protected ConstantStructure $element;

    /**
     * Test is next element is constant
     * @param string $content
     * @param int $start
     * @return bool
     */
    public static function nextElementIsConst(string $content, int $start): bool
    {
        $matches = preg_match_all('/' . static::REGEXP . '/', mb_substr($content, $start));

        return !empty($matches);
    }

    /**
     * @return ConstantStructure
     */
    public function getElement(): ConstantStructure
    {
        return $this->element;
    }

    /**
     * @param ConstantStructure $element
     * @return $this
     * @throws WrongElementClass
     */
    public function setElement($element): ConstElement
    {
        if (!$element instanceof ConstantStructure) {
            throw new WrongElementClass('Wrong argument type (' . $element::class . '). It must be ' . ConstantStructure::class);
        }

        $this->element = $element;
        return $this;
    }

    // Pase constant
    public function parse(string $content, int $start): int
    {
        $definitionArray = [];
        preg_match(
            '/' . static::REGEXP . '/',
            mb_substr($content, $start),
            $definitionArray
        );

        foreach ($definitionArray as $key => $part)
        {
            if ($key == 0) {
                continue;
            }

            if (in_array($part, ClassScope::listCasesAsString())) {
                $scope = ClassScope::getScopeFromString($part);
            } else if ($part != '') {
                if (!isset($name)) {
                    $name = $part;
                } else {
                    $end = static::getLineEndingPos($part, 0, true);
                    $this->setElement(new ConstantStructure($name, mb_substr($part, 0, $end), $scope ?? ClassScope::public));
                }
            }
        }

        return static::getLineEndingPos($content, $start, true) + 1;
    }

}