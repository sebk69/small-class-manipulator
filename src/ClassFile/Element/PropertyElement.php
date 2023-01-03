<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element;

use SmallClassManipulator\ClassFile\Element\Bean\TypedVarStructure;
use SmallClassManipulator\ClassFile\Element\Exception\WrongElementClass;
use SmallClassManipulator\ClassFile\Element\Enum\ClassScope;

class PropertyElement extends AbstractElement
{

    const REGEXP = '^[ \t\n\r]*(static)?[ \t\n\r]*(public|private|protected)?[ \t\n\r]*(static)?[ \t\n\r]*([a-z|A-Z]*)[ \t\n\r]*(\$[a-z|A-Z|0-9|\\_]*)[ \t\n\r]*?(=[ \t\n\r]*([\S\s]*))?[ \t\n\r]*;';

    protected TypedVarStructure $element;

    public static function nextElementIsProperty(string $content, int $start): bool
    {
        $count = preg_match_all('/' . static::REGEXP . '/', mb_substr($content, $start));

        return !empty($count);
    }

    /**
     * @return PropertyElement
     */
    public function getElement(): TypedVarStructure
    {
        return $this->element;
    }

    /**
     * @param TypedVarStructure $element
     * @return $this
     * @throws WrongElementClass
     */
    public function setElement($element): PropertyElement
    {
        if (!$element instanceof TypedVarStructure) {
            throw new WrongElementClass('Wrong argument type (' . $element::class . '). It must be ' . PropertyElement::class);
        }

        $this->element = $element;
        return $this;
    }

    /**
     * @param string $content
     * @param int $start
     * @return int
     * @throws Exception\ClassScopeException
     */
    public function parse(string $content, int $start): int
    {
        $definitionArray = [];
        preg_match(
            '/' . static::REGEXP . '/',
            mb_substr($content, $start),
            $definitionArray
        );

        $isStatic = false;
        foreach ($definitionArray as $key => $part)
        {
            if ($key == 0) {
                continue;
            }

            if ($part == 'static') {
                $isStatic = true;
            } else if (in_array($part, ClassScope::listCasesAsString())) {
                $scope = ClassScope::getScopeFromString($part);
            } else if ($part != '') {
                if (!isset($name) && substr($part, 0, 1) == '$') {
                    $name = $part;
                } if (!isset($name)) {
                    $type = $part;
                } else {
                    $end = static::getLineEndingPos($part, 0, true);
                    $value = mb_substr($part, 0, $end);
                }
            }
        }

        $this->setElement((new TypedVarStructure($name, $type ?? null, $value ?? null, $scope ?? null, $isStatic)));

        return static::getLineEndingPos($content, $start, true) + 1;
    }

}