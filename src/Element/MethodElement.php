<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Element;

use Couchbase\ScopeSpec;
use Sebk\SmallClassManipulator\Element\Bean\TypedVarStructure;
use Sebk\SmallClassManipulator\Element\Trait\ClassScoped;
use Sebk\SmallClassManipulator\Element\Trait\ClassScopes;
use Sebk\SmallClassManipulator\Exception\AlreadyExistsException;
use Sebk\SmallClassManipulator\Exception\NotFoundException;
use Sebk\SmallClassManipulator\Exception\SyntaxErrorException;
use function Webmozart\Assert\Tests\StaticAnalysis\boolean;

class MethodElement extends BaseElement
{

    const REGEXPR = '(static)?[ \t\n\r]*(public|private|protected)?[ \t\n\r]*(static)?[ \t\n\r]*function[ \t\n\r][ \t\n\r]*([\_|a-z|A-Z|0-9]*)[ \t\n\r]*\(';

    use ClassScoped;
    protected string $name;
    /** @var TypedVarStructure[] */
    protected array $parameters;
    protected string $content;

    public static function nextElementIsMethod(string $content, int $start): bool
    {
        return preg_match('/' . static::REGEXPR . '/', mb_substr($content, $start));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MethodElement
     */
    public function setName(string $name): MethodElement
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param TypedVarStructure $parameter
     * @return MethodElement
     */
    public function addParameter(TypedVarStructure $parameter): static
    {
        if (array_key_exists($parameter->getName(), $this->parameters)) {
            throw new AlreadyExistsException('Can\'t add parameter \'' . $parameter->getName() . '\' : it aleady exists');
        }

        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $silentFail
     * @return $this
     * @throws NotFoundException
     */
    public function removeParameter(string $name, bool $silentFail = true): static
    {
        if (!array_key_exists($name, $this->parameters)) {
            if (!$silentFail) {
                throw new NotFoundException('Can\'t find parameter \'' . $name . '\' : not found');
            }
            return $this;
        }

        unset($this->parameters[$name]);

        return $this;
    }

    /**
     * @param string $content
     * @return MethodElement
     */
    public function setContent(string $content): MethodElement
    {
        $this->content = $content;
        return $this;
    }

    public function parse(string $content, int $start): int
    {
        $definitionArray = [];
        preg_match(
            '/' . static::REGEXPR . '/',
            mb_substr($content, $start),
        $definitionArray
        );
        var_dump(substr($content, $start));

        foreach ($definitionArray as $key => $part)
        {
            if ($key == 0) {
                continue;
            }

            if ($part == 'static') {
                $this->setStatic(true);
            } else if (in_array($part, ClassScopes::listCasesAsString())) {
                $this->setScope(ClassScopes::getScopeFromString($part));
            } else if ($part != '') {
                $this->setName($part);
            }
        }
        $start = $this->parseParameters($content, $start + mb_strlen($definitionArray[0]));
        $this->setElement(trim(mb_substr($content, $start + 1, ($end = $this->findEndBracket($content, $start)) - $start + 1)));

        return $end + 1;
    }

    protected function parseParameters($content, $start): int
    {
        for ($end = $start; $end < mb_strlen($content) && $content[$end] != ')'; $end++);

        if ($content[$end] != ')') {
            throw new SyntaxErrorException('Unexpected end of file reading definition of ' . $this->getName());
        }

        if ($end - $start > 0) {
            $parameters = explode(',', mb_substr($content, $start, $end - $start));
            foreach ($parameters as $parameter) {
                $exploded = explode(' ', trim($parameter));
                $final = [];
                foreach ($exploded as $key => $raw) {
                    if (trim($raw) != '') {
                        $final[] = trim($raw);
                    }
                }

                var_dump($final);
                if (count($final) == 1) {
                    $structParam = new TypedVarStructure($final[0]);
                } else if (count($final) == 2 && in_array($final[0], ClassScopes::listCasesAsString())) {
                    $structParam = new TypedVarStructure($final[1], null, ClassScopes::getScopeFromString($final[0]));
                } else if (count($final) == 2 && !in_array($final[0], ClassScopes::listCasesAsString())) {
                    $structParam = new TypedVarStructure($final[1], $final[0]);
                } else {
                    $structParam = new TypedVarStructure($final[2], $final[1], ClassScopes::getScopeFromString($final[0]));
                }
                $this->addParameter($structParam);
            }
        }

        return $end + 2;
    }

    // TODO add string parsing
    protected function findEndBracket($content, $start): int
    {
        $levelMap = [];
        $started = false;
        for ($i = $start; $i < mb_strlen($content); $i++) {
            if (in_array(mb_substr($content, $i, 1), ['{', '[', '('])) {
                $levelMap[count($levelMap)] = mb_substr($content, $i, 1);
                $started = true;
            }
            if (in_array(mb_substr($content, $i, 1), ['}', ']', ')'])) {
                if (!$started) {
                    throw new SyntaxErrorException('Syntax error in definition of ' . $this->getName());
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