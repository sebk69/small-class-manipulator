<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element;

use Sebk\SmallClassManipulator\ClassFile\Element\Bean\MethodStructure;
use Sebk\SmallClassManipulator\ClassFile\Element\Bean\TypedVarStructure;
use Sebk\SmallClassManipulator\ClassFile\Element\Exception\WrongElementClass;
use Sebk\SmallClassManipulator\ClassFile\Element\Trait\ClassScoped;
use Sebk\SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class MethodElement extends AbstractElement
{

    const REGEXP = '^[ \t\n\r]*(static)?[ \t\n\r]*(public|private|protected)?[ \t\n\r]*(static)?[ \t\n\r]*function[ \t\n\r][ \t\n\r]*([\_|a-z|A-Z|0-9]*)[ \t\n\r]*\(';
    const PARAM_REGEXP = '[ \t\n\r]*(public|private|protected)?[ \t\n\r]*([A-Z|a-z]*)[ \t\n\r]*(\$[\_|a-z|A-Z|0-9]*)[=(\S\s)]?';

    protected MethodStructure $element;

    /**
     * Is next element is a method ?
     * @param string $content
     * @param int $start
     * @return bool
     */
    public static function nextElementIsMethod(string $content, int $start): bool
    {
        $matches = preg_match_all('/' . static::REGEXP . '/', mb_substr($content, $start));

        return !empty($matches);
    }

    /**
     * @return MethodStructure
     */
    public function getElement(): MethodStructure
    {
        return $this->element;
    }

    /**
     * @param MethodStructure $element
     * @return $this
     * @throws WrongElementClass
     */
    public function setElement($element): MethodElement
    {
        if (!$element instanceof MethodStructure) {
            throw new WrongElementClass('Wrong argument type (' . $element::class . '). It must be ' . MethodStructure::class);
        }

        $this->element = $element;
        return $this;
    }

    /**
     * Parse method
     * @param string $content
     * @param int $start
     * @return int
     * @throws Exception\ClassScopeException
     * @throws SyntaxErrorException
     * @throws WrongElementClass
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
                $name = $part;
            }
        }
        $this->setElement(new MethodStructure($name));
        $this->getElement()->setScope($scope);
        $this->getElement()->setStatic($isStatic);
        $start = $this->parseParameters($content, $start + mb_strlen($definitionArray[0]));
        if (mb_substr($content, $start, 1) == ':') {
            for ($i = $start + 1; !in_array(mb_substr($content, $i + 1, 1), ['{', ';']); $i++);
            $this->getElement()->setReturnType(trim(mb_substr($content, $start + 1, $i - $start - 1)));
            $start = $i;
        }
        $this->getElement()->setContent(trim(mb_substr($content, $start, $this->findEndBracket($content, $start) - $start + 1)));

        return $this->findEndBracket($content, $start) + 1;
    }

    /**
     * Parse parameters
     * @param $content
     * @param $start
     * @return int
     * @throws Exception\ClassScopeException
     * @throws SyntaxErrorException
     * @throws \Sebk\SmallClassManipulator\ClassFile\Exception\AlreadyExistsException
     */
    protected function parseParameters($content, $start): int
    {
        for ($end = $start; $end < mb_strlen($content) && $content[$end] != ')'; $end++);

        if ($content[$end] != ')') {
            throw new SyntaxErrorException('Unexpected end of file reading definition of ' . $this->getName());
        }

        $end--;
        if ($end - $start > 0) {
            $parameters = explode(',', mb_substr($content, $start, $end - $start));
            foreach ($parameters as $parameter) {
                $final = [];
                preg_match('/' . static::PARAM_REGEXP . '/', $parameter, $final);

                $structParam = new TypedVarStructure($final[3], $final[2] != '' ? $final[2] : null, $final[4] ?? null, !empty($final[1]) ? ClassScope::getScopeFromString($final[1]) : null);
                $this->getElement()->addParameter($structParam);
            }
        }

        return $end + 1;
    }

    /**
     * Find the end of bracket
     * @param $content
     * @param $start
     * @return int
     * @throws SyntaxErrorException
     */
    protected function findEndBracket($content, $start): int
    {
        $levelMap = [];
        $started = false;
        for ($i = $start; $i < mb_strlen($content); $i++) {
            if (in_array(mb_substr($content, $i, 1), ['"', '\''])) {
                $i = static::findEndOfString($content, $i);
            }
            if (in_array(mb_substr($content, $i, 1), ['{', '[', '('])) {
                $levelMap[] = mb_substr($content, $i, 1);
                $started = true;
            }
            if (in_array(mb_substr($content, $i, 1), ['}', ']', ')'])) {
                if (!$started) {
                    throw new SyntaxErrorException('Syntax error in definition of ' . $this->element->getName());
                }
                $lastLevel = $levelMap[count($levelMap) - 1];
                $found = true;
                switch (mb_substr($content, $i, 1)) {
                    case ')':
                        if ($lastLevel != '(') {
                            $found = false;
                        }
                        break;
                    case '}':
                        if ($lastLevel != '{') {
                            $found = false;
                        }
                        break;
                    case ']':
                        if ($lastLevel != '[') {
                            $found = false;
                        }
                        break;
                }
                if (!$found) {
                    throw new SyntaxErrorException('Syntax error : unexpected ' . mb_substr($content, $i, 1) . ' in definition of ' . $this->getName());
                }
                unset($levelMap[count($levelMap) - 1]);
            }

            if (count($levelMap) == 0 && $started) {
                return $i;
            }
        }

        throw new SyntaxErrorException('Unexpected end of file reading definition of ' . $this->getName());
    }

}