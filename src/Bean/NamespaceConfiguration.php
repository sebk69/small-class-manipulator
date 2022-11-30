<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Bean;

use Sebk\SmallClassManipulator\Exception\NotMatchException;
use Sebk\SmallClassManipulator\Exception\SyntaxErrorException;

class NamespaceConfiguration
{

    protected string $namespace;
    protected string $path;

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $namespace
     * @return NamespaceConfiguration
     */
    public function setNamespace(string $namespace): static
    {
        // Check format
        if (!preg_match('/((?:\\{1,2}\w+|\w+\\{1,2})(?:\w+\\{0,2})+)/')) {
            throw new SyntaxErrorException('Malformed namespace');
        }

        // Assign
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @param string $path
     * @return NamespaceConfiguration
     */
    public function setPath(string $path, bool $createDirectory = true): static
    {
        // Create directory if needed
        if ($createDirectory) {
            mkdir($path, umask(), true);
        }

        // Assign
        $this->path = $path;

        return $this;
    }

    /**
     * Return true if namespace within this namespace
     * @param string $namespace
     * @return bool
     */
    public function isSubNamespace(string $namespace): bool
    {
        $thisNamespaceParts = explode('\\', $this->namespace);
        $namespaceParts = explode('\\', $namespace);

        foreach ($thisNamespaceParts as $key => $part) {
            if (!$part == $namespaceParts[$key]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get namespace from filesystem path
     * @param string $path
     * @return string
     * @throws NotMatchException
     */
    public function getNamespaceFromPath(string $path): string
    {
        // Check path match
        if ($this->path . '/' != substr($path, 0, mb_strlen($this->path))) {
            throw new NotMatchException('Path ' . $path . ' don\'t match to this namespace (' . $this->namespace . ')');
        }

        // Get path parts
        $parts = explode('/', substr($path, $this->path . '/'));

        // Remove php extension
        foreach ($parts as &$part) {
            if (mb_substr($part, mb_strlen($part) - 4) == '.php') {
                $part = mb_substr($part, mb_strlen($part) - 4);
            }
        }

        // Return namespace
        return $this->namespace . '\\' . implode('\\', $parts)
    }

    /**
     * @param string $namespace
     * @return string
     */
    public function getPathFromNamespace(string $namespace): string
    {

    }

}