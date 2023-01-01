<?php /** @noinspection PhpUndefinedVariableInspection */

/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile;

use Sebk\SmallClassManipulator\ClassFile\Element\AbstractElement;
use Sebk\SmallClassManipulator\ClassFile\Element\BaseElement;
use Sebk\SmallClassManipulator\ClassFile\Element\Bean\ClassContentStructure;
use Sebk\SmallClassManipulator\ClassFile\Element\ConstElement;
use Sebk\SmallClassManipulator\ClassFile\Element\MethodElement;
use Sebk\SmallClassManipulator\ClassFile\Element\PropertyElement;
use Sebk\SmallClassManipulator\ClassFile\Logic\ClassContentParser;
use Sebk\SmallClassManipulator\ClassFile\Exception\ClassNotPhpException;
use Sebk\SmallClassManipulator\ClassFile\Exception\NotFoundException;
use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class ClassFile
{

    const PHP_START = '<?php';

    protected string $content;

    protected BaseElement $namespace;

    protected BaseElement $classname;

    /** @var BaseElement[] */
    protected array $uses = [];
    protected BaseElement|null $extends;
    /** @var BaseElement[] */
    protected array $implements = [];
    protected ClassContentStructure $contentStructure;
    protected bool $isFinal;
    protected bool $isAbstract;

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
            throw new NotFoundException('File ' . $filepath . ' not found !');
        }

        $this->content = file_get_contents($filepath);

        $this->parse();

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
     * @return BaseElement
     */
    public function getNamespace(): BaseElement
    {
        return $this->namespace;
    }

    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return $this->isFinal;
    }

    /**
     * @param bool $isFinal
     * @return ClassFile
     */
    public function setIsFinal(bool $isFinal): ClassFile
    {
        $this->isFinal = $isFinal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }

    /**
     * @param bool $isAbstract
     * @return ClassFile
     */
    public function setIsAbstract(bool $isAbstract): ClassFile
    {
        $this->isAbstract = $isAbstract;
        return $this;
    }

    /**
     * @return BaseElement
     */
    public function getClassname(): BaseElement
    {
        return $this->classname;
    }

    /**
     * @return BaseElement[]
     */
    public function getUses(): array
    {
        return $this->uses;
    }

    /**
     * @return BaseElement
     */
    public function getExtends(): BaseElement|null
    {
        return $this->extends;
    }

    /**
     * @return array
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @return ClassContentStructure
     */
    public function getContentStructure(): ClassContentStructure
    {
        return $this->contentStructure;
    }

    /**
     * @param string $content
     * @return ClassFile
     */
    public function setContent(string $content): ClassFile
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param BaseElement $classname
     * @return ClassFile
     */
    public function setClassname(BaseElement $classname): ClassFile
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * @param array $uses
     * @return ClassFile
     */
    public function setUses(array $uses): ClassFile
    {
        $this->uses = $uses;
        return $this;
    }

    /**
     * @param BaseElement|null $extends
     * @return ClassFile
     */
    public function setExtends(?BaseElement $extends): ClassFile
    {
        $this->extends = $extends;
        return $this;
    }

    /**
     * @param array $implements
     * @return ClassFile
     */
    public function setImplements(array $implements): ClassFile
    {
        $this->implements = $implements;
        return $this;
    }

    /**
     * @param ClassContentStructure $contentStructure
     * @return ClassFile
     */
    public function setContentStructure(ClassContentStructure $contentStructure): ClassFile
    {
        $this->contentStructure = $contentStructure;
        return $this;
    }

    /**
     * Parse file
     * @return void
     * @throws ClassNotPhpException
     * @throws SyntaxErrorException
     */
    protected function parse(): void
    {
        $codeStart = $this->parseIsPhp();
        $endOfNamespace = $this->parseNamespace($codeStart);
        $endOfUse = $this->parseUses($endOfNamespace);
        $endOfClassname = $this->parseClassname($endOfUse);
        $endOfExtends = $this->parseExtends($endOfClassname);
        $endOfImplements = $this->parseImplements($endOfExtends);
        $commentData = AbstractElement::parseAfter($this->content, $endOfImplements);
        $this->getClassname()->setLineComment($commentData['comment']);
        $this->contentStructure = $this->parseClassContent($this->content, $commentData['newStart']);
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
     * @param int $codeStart
     * @return int
     * @throws SyntaxErrorException
     */
    private function parseNamespace(int $codeStart): int
    {
        // Create element
        $element = new BaseElement();
        $commentData = AbstractElement::parseBefore($this->content, $codeStart);
        $codeStart = $commentData['newStart'];
        $element->setCommentBefore($commentData['comment']);

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

        $commentData = AbstractElement::parseAfter($this->content, $i);
        $element->setLineComment($commentData['comment']);

        return $commentData['newStart'] + 1;
    }

    private function parseUses(int $start): int
    {
        $savedStart = $start;
        // Create element
        $element = new BaseElement();
        $commentData = AbstractElement::parseBefore($this->content, $start);
        $start = $commentData['newStart'];
        $element->setCommentBefore($commentData['comment']);

        // Set keywords
        $useKeyword = 'use';
        $classKeyword = 'class ';
        $skipChars = [' ', "\t", "\n"];

        // Scan use keyword
        for (
            $i = $start;
            $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen($useKeyword)) != $useKeyword &&
                mb_substr($this->content, $i, mb_strlen($classKeyword)) != $classKeyword;
            $i++
        ) {};
        $startOfThisUse = $i + mb_strlen($useKeyword);

        // End of uses
        if (mb_substr($this->content, $i, mb_strlen($useKeyword)) != $useKeyword) {
            return $savedStart;
        }

        // Keyword found, get string to the end of instruction
        $i = BaseElement::getLineEndingPos($this->content, $startOfThisUse, false);
        $this->uses[] = $element->setElement(trim(mb_substr($this->content, $startOfThisUse, $i - $startOfThisUse)));
        $commentData = AbstractElement::parseAfter($this->content, $i + 1);
        $element->setLineComment($commentData['comment']);
        $ends = $commentData['newStart'];

        return $this->parseUses($ends);
    }

    /**
     * Parse classname
     * @param int $start
     * @return int
     * @throws SyntaxErrorException
     */
    private function parseClassname(int $start): int
    {
        // Create element
        $element = new BaseElement();
        $commentData = AbstractElement::parseBefore($this->content, $start);
        $start = $commentData['newStart'];
        $element->setCommentBefore($commentData['comment']);

        // Find class keywords
        for ($i = $start + 1; $i < mb_strlen($this->content); $i++) {
            preg_match('/(final[ \t\n\r]*class|abstract[ \t\n\r]*class|class)/', mb_substr($this->content, $i), $match);
            $needle = $match[0];
            switch ($needle) {
                case 'final class':
                    $this->isFinal = true;
                    $this->isAbstract = false;
                    break 2;
                case 'abstract class':
                    $this->isFinal = false;
                    $this->isAbstract = true;
                    break 2;
                case 'class':
                    $this->isFinal = false;
                    $this->isAbstract = false;
                    break 2;
            }
        }

        // Not found
        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Can\'t find class keyword');
        }

        $startClassName = $i + mb_strlen($needle) + 1;

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

        return $i;
    }

    /**
     * Parse extended class
     * @param $start
     * @return int
     * @throws SyntaxErrorException
     */
    private function parseExtends($start): int
    {
        // Create element
        $element = new BaseElement();
        $commentData = AbstractElement::parseBefore($this->content, $start);
        $start = $commentData['newStart'];
        $element->setCommentBefore($commentData['comment']);

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

        $commentData = AbstractElement::parseAfter($this->content, $i);
        $element->setLineComment($commentData['comment']);

        return $commentData['newStart'];
    }

    /**
     * Parse interfaces
     * @param $start
     * @return int
     * @throws SyntaxErrorException
     */
    private function parseImplements($start): int
    {
        // Find implements keyword
        for ($i = $start; $i < mb_strlen($this->content) && mb_substr($this->content, $i, mb_strlen('implements')) != 'implements'; $i++);

        if ($i >= mb_strlen($this->content)) {
            return $start;
        }

        // Set start
        $startImplements = mb_strlen('implements') + $i;

        // Find end of implents classname
        for ($i = $startImplements; $i < mb_strlen($this->content) && mb_substr($this->content, $i, 1) != '{' && mb_substr($this->content, $i, 2) != '//'; $i++);

        if ($i >= mb_strlen($this->content)) {
            throw new SyntaxErrorException('Start class missing !');
        }

        if (trim(mb_substr($this->content, $startImplements, $i - $startImplements - 1) == '')) {
            $this->implements = [];
            return $i;
        }

        $this->implements = array_map(function ($interface) {
            return $this->implements[] = trim($interface);
        }, explode(',', trim(mb_substr($this->content, $startImplements, $i - $startImplements))));

        return $i;
    }

    /**
     * Parse class content
     * @param string $content
     * @param int $start
     * @return ClassContentStructure
     * @throws Element\Exception\ClassScopeException
     * @throws Element\Exception\WrongElementClass
     * @throws SyntaxErrorException
     */
    public function parseClassContent(string $content, int $start): ClassContentStructure
    {
        for (; $start < mb_strlen($content) && in_array($content[$start], [' ', '\t', '\n', '\r']) && $content[$start] != '{'; $start++);
        if ($start >= mb_strlen($content)) {
            throw new SyntaxErrorException('Can\'t find start of class content');
        }
        $start++;

        $result = new ClassContentStructure();
        while ($start < mb_strlen($content)) {
            $commentData = AbstractElement::parseBefore($content, $start);
            $start = $commentData['newStart'];

            if (PropertyElement::nextElementIsProperty($content, $start)) {
                $element = new PropertyElement();
                $element->setCommentBefore($commentData['comment']);
                $start = $element->parse($content, $start);
                $after = AbstractElement::parseAfter($content, $start);
                $start = $after['newStart'];
                $element->setLineComment($after['comment']);
                $result->addProperty($element);
            } else if (ConstElement::nextElementIsConst($content, $start)) {
                $element = new ConstElement();
                $element->setCommentBefore($commentData['comment']);
                $start = $element->parse($content, $start);
                $after = AbstractElement::parseAfter($content, $start);
                $start = $after['newStart'];
                $element->setLineComment($after['comment']);
                $result->addConst($element);
            } else if (MethodElement::nextElementIsMethod($content, $start)) {
                // Is method
                $element = new MethodElement();
                $element->setCommentBefore($commentData['comment']);
                $start = $element->parse($content, $start);
                $result->addMethod($element);
            }

            if (!isset($element) && trim(mb_substr($content, $start)) != '}') {
                throw new SyntaxErrorException('Can\'t find end of class content');
            }

            if (!isset($element)) {
                return $result;
            }

            unset($element);
        }

        return $result;
    }

    public function generate(string $filePath): self
    {
        $output = self::PHP_START . "\n";

        file_put_contents($filePath, $output);

        return $this;
    }

}