<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Element;

use Sebk\SmallClassManipulator\Element\Bean\ClassContentStructure;
use Sebk\SmallClassManipulator\Exception\SyntaxErrorException;

class ClassContentParser
{

    public function __construct() {}

    public function parse(string $content, int $start): ClassContentStructure
    {
        for (; $start < mb_strlen($content) && in_array($content[$start], [' ', '\t', '\n', '\r']) && $content[$start] != '{'; $start++);
        if ($start >= mb_strlen($content)) {
            throw new SyntaxErrorException('Can\'t find start of class content');
        }
        $start++;

        $result = new ClassContentStructure();
        while ($start < mb_strlen($content)) {
            $baseElement = new BaseElement();
            $element = null;
            $start = $baseElement->parseBefore($content, $start);

            if (MethodElement::nextElementIsMethod($content, $start)) {
                // Is method
                $element = new MethodElement();
                $element->setCommentBefore($baseElement->getCommentBefore());
                $start = $element->parse($content, $start);
                $result->addMethod($element);
            }

            if (PropertyElement::nextElementIsProperty($content, $start)) {
                $element = new PropertyElement();
            }

            if ($element == null && trim(mb_substr($content, $start)) != '}') {
                throw new SyntaxErrorException('Can\'t find end of class content');
            }
        }

        return $result;
    }

}