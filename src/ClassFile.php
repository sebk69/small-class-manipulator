<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator;

use Sebk\SmallClassManipulator\Element\BaseElement;
use Sebk\SmallClassManipulator\Element\Bean\ClassContentStructure;
use Sebk\SmallClassManipulator\Element\ClassContent;
use Sebk\SmallClassManipulator\Element\ClassContentParser;
use Sebk\SmallClassManipulator\Exception\ClassNotPhpException;
use Sebk\SmallClassManipulator\Exception\SyntaxErrorException;

class ClassFile
{

    const PHP_START = '<?php';

    protected string $content;

    protected BaseElement $namespace;

    protected BaseElement $classname;

    /** @var BaseElement[] */
    protected array $uses = [];

    protected BaseElement $extends;

    protected BaseElement $implements;

    protected ClassContentStructure $contentStructure;

    /**
     * Read file content and parse
     * @param string $filepath
     * @return $this
     * @throws ClassNotPhpException
     * @throws SyntaxErrorException
     */
    public function fromFile(string $filepath): static
    {
        if (!is_file($filepath)) {
            throw NotFoundException('File ' . $filepath . ' not found !');
        }

        $this->content = file_get_contents($filepath);

        $this->parse();

        return $this;
    }

    /**
     * Parse file
     * @return void
     * @throws ClassNotPhpException
     * @throws SyntaxErrorException
     */
    protected function parse()
    {
        $codeStart = $this->parseIsPhp();
        $endOfNamespace = $this->parseNamespace($codeStart);
        $endOfUse = $this->parseUses($endOfNamespace);
        $endOfClassname = $this->parseClassname($endOfUse);
        $endOfExtends = $this->parseExtends($endOfClassname);
        $endOfImplements = $this->parseImplements($endOfExtends);
        $classContent = new ClassContentParser();
        $this->contentStructure = $classContent->parse($this->content, $endOfImplements);
    }

    /*
     * Parse php tag, throw error if not found
     * @return $this
     * @throws ClassNotPhpException
     */
    private function parseIsPhp(): int
    {
        for ($i = 0; $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen(self::PHP_START)) != self::PHP_START; $i++);

        if (mb_substr($this->content, $i, mb_strlen(self::PHP_START)) == self::PHP_START) {
            return $i + mb_strlen(self::PHP_START);
        }

        throw new ClassNotPhpException('Class template must contains \'<?php\' at the beginning of file');
    }

    /**
     * Parse namespace
     * @return $this
     * @throws SyntaxErrorException
     */
    private function parseNamespace(int $codeStart): int
    {
        // Create element
        $element = new BaseElement();
        $codeStart = $element->parseBefore($this->content, $codeStart);

        // Find namespace keyword
        $needle = 'namespace ';
        for ($i = $codeStart; $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen($needle)) != $needle; $i++);

        if (mb_substr($this->content, $i, mb_strlen($needle)) != $needle) {
            throw new SyntaxErrorException('Can\'t find namespace keyword');
        }

        // Set start of namespace
        $start = $i + mb_strlen($needle);

        // Find end of namespace
        for ($i = $start; $i < mb_strlen($this->content) && mb_substr($this->content, $i, 1) != ';'; $i++);

        // Set namespace
        $element->setElement(mb_substr($this->content, $start, $i - $start));
        $this->namespace = $element;

        $ends = $element->parseAfter($this->content, $i + 1);

        return $ends;
    }

    private function parseUses(int $start): int
    {
        // Create element
        $element = new BaseElement();
        $start = $element->parseBefore($this->content, $start);

        // Set keywords
        $useKeyword = 'use';
        $skipChars = [' ', "\t", "\n"];

        // Scan use keyword
        for (
            $i = $start;
            ($i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen($useKeyword)) != $useKeyword) ||
                ($i < mb_strlen($this->content) && in_array($this->content[$i], $skipChars));
            $i++
        );

        // End of uses
        if (mb_substr($this->content, $i, mb_strlen($useKeyword)) != $useKeyword) {
            return $start;
        }

        // End of file
        if ($i >= mb_strlen($this->content)) {
            return $start;
        }

        // Keyword found, get string to the end of instruction
        $endOfInstruction = ';';
        $startOfThisUse = $i + mb_strlen($useKeyword) + 1;
        for ($i = $startOfThisUse; $i < mb_strlen($this->content) && $this->content[$i] != $endOfInstruction; $i++);
        $this->uses[] = $element->setElement(trim(mb_substr($this->content, $startOfThisUse, $i - $startOfThisUse)));
        $ends = $element->parseAfter($this->content, $i + 1);

        return $this->parseUses($ends);
    }

    /**
     * Parse classname
     * @return $this
     * @throws SyntaxErrorException
     */
    private function parseClassname(int $start): int
    {
        // Create element
        $element = new BaseElement();
        $start = $element->parseBefore($this->content, $start);

        // Find class keywords
        for ($i = $start + 1; $i < mb_strlen($this->content); $i++) {
            $needles = [
                'final class',
                'abstract class',
                'class'
            ];
            foreach ($needles as $needle) {
                if (mb_substr($this->content, $i, mb_strlen($needle)) == $needle) {
                    break 2;
                }
            }
        }

        // Not found
        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Can\'t find class keyword');
        }

        $startClassName = $i + mb_strlen($needle);

        // Find start of class name
        for ($i = $startClassName + 1; $i < mb_strlen($this->content); $i++)
        {
            if (
                mb_substr($this->content, $i, 1) == '{' ||
                mb_substr($this->content, $i, mb_strlen('extends')) == 'extends' ||
                mb_substr($this->content, $i, mb_strlen('implements')) == 'implements'
            ) {
                break;
            }
        }

        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Can\'t find start of class definition');
        }

        $this->classname = $element->setElement(trim(mb_substr($this->content, $startClassName, $i - $startClassName)));

        return $element->parseAfter($this->content, $i);
    }

    private function parseExtends($start): int
    {
        // Create element
        $element = new BaseElement();
        $start = $element->parseBefore($this->content, $start);

        // Find extends keyword
        for ($i = $start; $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen('extends')) != 'extends'; $i++);

        if ($i >= mb_strlen($this->content)) {
            $this->extends = null;
            return $start;
        }

        // Set start
        $startExtends = mb_strlen('extends') + $i;

        // Find end of extends classname
        for ($i = $startExtends; $i < mb_strlen($this->content) &&
            mb_substr($this->content, $i, mb_strlen('implements')) != 'implements' &&
            mb_substr($this->content, $i, 1) != '{'
        ; $i++);

        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Start class missing !');
        }

        if (trim(mb_substr($this->content, $startExtends, $i -$startExtends) == '')) {
            $this->extends = null;
            return $i;
        }

        $this->extends = $element->setElement(trim(mb_substr($this->content, $startExtends, $i -$startExtends)));

        return $element->parseAfter($this->content, $i);
    }

    private function parseImplements($start): int
    {
        // Create element
        $element = new BaseElement();
        $start = $element->parseBefore($this->content, $start);

        // Find extends keyword
        for ($i = $start; $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen('implements')) != 'implements'; $i++);

        if ($i >= mb_strlen($this->content)) {
            $this->implements = null;
            return $start;
        }

        // Set start
        $startImplents = mb_strlen('implements') + $i;

        // Find end of extends classname
        for ($i = $startImplents; $i < mb_strlen($this->content) &&
        mb_substr($this->content, $i, 1) != '{'
        ; $i++);

        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Start class missing !');
        }

        if (trim(mb_substr($this->content, $startImplents, $i - $startImplents) == '')) {
            $this->implements = null;
            return $i;
        }

        $this->implements = $element->setElement(array_map(function ($interface) {
            return trim($interface);
        }, explode(',', trim(mb_substr($this->content, $startImplents, $i - $startImplents)))));

        return $element->parseAfter($this->content, $i);
    }

}