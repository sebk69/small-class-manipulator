<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element;

class PropertyElement extends BaseElement
{

    const REGEXPR = '^(static)?[ \t\n\r]*(public|private|protected)?[ \t\n\r]*(static)?[ \t\n\r]*([a-z|A-Z]*)[ \t\n\r]*(\$[a-z|A-Z|0-9|\_]*)[ \t\n\r]*=[ \t\n\r]*([\S\s]*);';

    public static function nextElementIsProperty(string $content, int $start): bool
    {
        return preg_match('/' . static::REGEXPR . '/', mb_substr($content, $start));
    }

    public static function pa


}