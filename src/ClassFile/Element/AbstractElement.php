<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\ClassFile\Element;

use Sebk\SmallClassManipulator\ClassFile\Exception\SyntaxErrorException;

abstract class AbstractElement
{
    const whiteChars = " \t\n\r";
    const lineWhiteChars = " \t";
    const lineEnds = "\n\r";
    const commentsStarts = ['/*', '//'];

    protected string|null $commentBefore = null;
    protected string|null $lineComment = null;

    /**
     * @return string|null
     */
    public function getCommentBefore(): ?string
    {
        return $this->commentBefore;
    }

    /**
     * @param string|null $commentBefore
     * @return static
     */
    public function setCommentBefore(?string $commentBefore): static
    {
        $this->commentBefore = $commentBefore;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLineComment(): ?string
    {
        return $this->lineComment;
    }

    /**
     * @param string|null $LineComment
     * @return static
     */
    public function setLineComment(?string $lineComment): static
    {
        $this->lineComment = $lineComment;
        return $this;
    }

    /**
     * @return mixed
     */
    abstract public function getElement();

    /**
     * @param mixed $element
     * @return static
     */
    abstract public function setElement($element): self;

    /**
     * Parse before element
     * @param string $content
     * @param int $start
     * @param $previousComment
     * @return array
     * @throws SyntaxErrorException
     */
    public static function parseBefore(string $content, int $start, $previousComment = ''): array
    {

        // Find comment start
        for ($i = $start;
             $i < mb_strlen($content) &&
             mb_substr($content, $i, 2) != '/*' &&
             mb_substr($content, $i, 2) != '//' &&
             in_array(mb_substr($content, $i, 1), str_split(static::whiteChars));
             $i++);

        if (mb_substr($content, $i, 2) == '/*') {
            $commentData = static::parseBlocComment($content, $i + 2);
            return static::parseBefore($content, $commentData['newStart'] + 1, $previousComment . $commentData['comment']);
        }

        if (mb_substr($content, $i, 2) == '//') {
            $commentData = static::parseLineComment($content, $i + 2);
            return static::parseBefore($content, $commentData['newStart'], $previousComment . $commentData['comment']);
        }

        return [
            'newStart' => $start,
            'comment' => $previousComment,
        ];

    }

    /**
     * Parse after element
     * @param string $content
     * @param $start
     * @return array
     */
    public static function parseAfter(string $content, $start): array
    {
        // Find comment start
        for ($i = $start;
             $i < mb_strlen($content) &&
             mb_substr($content, $i, 2) != '//' &&
             in_array(mb_substr($content, $i, 1), str_split(static::lineWhiteChars));
             $i++) {};

        if (mb_substr($content, $i, 2) == '//') {
            return static::parseLineComment($content, $i + 2);
        }

        return ['newStart' => $start, 'comment' => ''];
    }

    /**
     * Parse bloc comment
     * @param string $content
     * @param int $start
     * @return array
     * @throws SyntaxErrorException
     */
    private static function parseBlocComment(string $content, int $start): array
    {
        for ($i = $start;
             $i < mb_strlen($content) && mb_substr($content, $i, 2) != '*/';
             $i++);

        if ($i >= mb_strlen($content)) {
            throw new SyntaxErrorException('Can\'t find end of bloc comment !');
        }

        return [
            'comment' => mb_substr($content, $start, $i - $start) . "\n",
            'newStart' => $i + 1
        ];
    }

    /**
     * Parse line comment
     * @param string $content
     * @param int $start
     * @param $previousComment
     * @return array
     */
    private static function parseLineComment(string $content, int $start): array
    {
        for ($i = $start;
             !in_array(mb_substr($content, $i + 1, 1), str_split(static::lineEnds));
             $i++);

        $comment = mb_substr($content, $start, $i - $start + 1) . "\n";

        return ['comment' => $comment, 'newStart' => $i + 2];
    }

    /**
     * Get end position of current line
     * @param string $string
     * @param int $start
     * @param bool $escapeT_STRING
     * @param string $eolChar
     * @return string
     */
    public static function getLineEndingPos(string $string, int $start, bool $jumpStrings, string $eolChar = ';'): string
    {
        for ($i = $start; $i < mb_strlen($string) && mb_substr($string, $i, 1) != $eolChar; $i++) {
            if ($jumpStrings && in_array(mb_substr($string, $i, 1), ['\'', '"'])) {
                $i = static::findEndOfString($string, $i);
            }
        }

        return $i;
    }

    protected static function findEndOfString(string $string, int $start)
    {
        $i = $start;
        $endingChar = mb_substr($string, $i, 1);
        $i++;
        do {
            if (mb_substr($string, $i, 1) == '\\') {
                $i++;
            }
            $i++;
        } while (mb_substr($string, $i, 1) != $endingChar);

        return $i;
    }

    public function getFormatedCommentBefore(): string
    {
        return !empty(trim($this->commentBefore)) ? '/*' . substr($this->commentBefore, 0, strlen($this->commentBefore) - 1) . "*/\n" : '';
    }

    public function getFormatedLineComment(): string
    {
        $lineComment = trim(str_replace(["\n", "\r"], ' ', $this->getLineComment()));

        return !empty($lineComment) ? ' // ' . $lineComment : '';
    }

}