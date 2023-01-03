<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element;

use SmallClassManipulator\ClassFile\Element\Exception\WrongElementClass;

class TraitElement extends AbstractElement
{

    const REGEXP = '^[ \t\n\r]*use?[ \t\n\r]*([a-z|A-Z|0-9|_]*)[ \t\n\r]*;';

    protected string $name;

    /**
     * Test is next element is trait use
     * @param string $content
     * @param int $start
     * @return bool
     */
    public static function nextElementIsTrait(string $content, int $start): bool
    {
        $matches = preg_match_all('/' . static::REGEXP . '/', mb_substr($content, $start));

        return !empty($matches);
    }

    /**
     * @return string
     */
    public function getElement(): string
    {
        return $this->name;
    }

    /**
     * @param string $element
     * @return $this
     */
    public function setElement($element): TraitElement
    {
        $this->name = $element;
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

            $this->setElement($part);
        }

        return static::getLineEndingPos($content, $start, false) + 1;
    }

}