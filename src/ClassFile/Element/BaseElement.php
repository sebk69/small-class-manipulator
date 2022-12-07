<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element;

use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

class BaseElement
{

    const whiteChars = " \t\n\r";
    const lineWhiteChars = " \t";
    const lineEnds = "\n\r";
    const commentsStarts = ['/*', '//'];

    protected string|null $commentBefore = null;
    protected string|null $LineComment = null;
    protected mixed $element = null;

    /**
     * @return string|null
     */
    public function getCommentBefore(): ?string
    {
        return $this->commentBefore;
    }

    /**
     * @param string|null $commentBefore
     * @return BaseElement
     */
    public function setCommentBefore(?string $commentBefore): BaseElement
    {
        $this->commentBefore = $commentBefore;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLineComment(): ?string
    {
        return $this->LineComment;
    }

    /**
     * @param string|null $LineComment
     * @return BaseElement
     */
    public function setLineComment(?string $LineComment): BaseElement
    {
        $this->LineComment = $LineComment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getElement(): mixed
    {
        return $this->element;
    }

    /**
     * @param mixed $element
     * @return BaseElement
     */
    public function setElement(mixed $element): BaseElement
    {
        $this->element = $element;
        return $this;
    }

    public function parseBefore(string $content, int $start): int
    {
        // Find comment start
        for ($i = $start; 
             $i < mb_strlen($content) &&
             mb_substr($content, $i, 2) != '/*' &&
             mb_substr($content, $i, 2) != '//' &&
             in_array(mb_substr($content, $i, 1), str_split(static::whiteChars));
             $i++);

        if (mb_substr($content, $i, 2) == '/*') {
            $newStart = $this->parseBlocComment($content, $i + 2);
            return $this->parseBefore($content, $newStart);
        }

        if (mb_substr($content, $i, 2) == '//') {
            $newStart = $this->parseLineComment($content, $i + 2, true);
            return $this->parseBefore($content, $newStart);
        }

        return $start;
    }

    public function parseAfter(string $content, $start): int
    {
        // Find comment start
        for ($i = $start;
             $i < mb_strlen($content) &&
             mb_substr($content, $i, 2) != '//' &&
             in_array(mb_substr($content, $i, 1), str_split(static::whiteChars));
             $i++);

        if (mb_substr($content, $i, 2) == '//') {
            return $this->parseLineComment($content, $i + 2);
        }

        return $start;
    }
    
    private function parseBlocComment(string $content, int $start): int
    {
        for ($i = $start;
             $i < mb_strlen($content) && mb_substr($content, $i, 2) != '*/';
             $i++);
        
        if ($i >= mb_strlen($content)) {
            throw new SyntaxErrorException('Can\'t find end of bloc comment !');
        }
        
        $this->commentBefore .= mb_substr($content, $start, $i - $start) . "\n";

        return $i + 2;
    }

    private function parseLineComment(string $content, int $start, $before = false): int
    {
        for ($i = $start;
             !in_array(mb_substr($content, $i, 1), str_split(static::lineEnds));
             $i++);

        if ($before) {
            $this->commentBefore .= mb_substr($content, $start, $i - $start) . "\n";
        } else {
            $this->LineComment = mb_substr($content, $start, $i - $start);
        }

        return $i;
    }

}