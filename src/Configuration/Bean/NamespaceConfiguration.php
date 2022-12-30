<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Configuration\Bean;

use Sebk\SmallClassManipulator\Configuration\Exception\BadNamespace;
use Sebk\SmallClassManipulator\Configuration\Exception\NotMatchException;
use Sebk\SmallClassManipulator\Configuration\Exception\SyntaxErrorException;

class NamespaceConfiguration
{

    protected string $namespace;
    protected string $path;

    /**
     * @param string $namespace
     * @param string $path
     * @throws SyntaxErrorException
     */
    public function __construct(string $namespace, string $path)
    {
        $this->setNamespace($namespace);
        $this->setPath($path);
    }

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
     * Check namespace syntax and set namespace
     * @param string $namespace
     * @return $this
     * @throws SyntaxErrorException
     */
    public function setNamespace(string $namespace): static
    {
        // Check format
        if (preg_match('/((?:\\{1,2}\w+|\w+\\{1,2})(?:\w+\\{0,2})+)/', $namespace) === false) {
            throw new SyntaxErrorException('Malformed namespace (' . $namespace .')');
        }

        // Set as from root namespace
        if (str_starts_with($namespace, '\\')) {
            $namespace = substr($namespace, 1);
        }

        // Assign
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set path and create it if not exists
     * @param string $path
     * @param bool $createDirectory
     * @return $this
     */
    public function setPath(string $path, bool $createDirectory = true): static
    {
        // Create directory if needed
        if ($createDirectory) {
            @mkdir($path, umask(), true);
        }

        // Assign
        $this->path = $path;

        return $this;
    }

    /**
     * Check namespace included into configured namespace
     * Return true if namespace within this namespace
     * @param string $namespace
     * @return bool
     */
    public function isSubNamespace(string $namespace): bool
    {
        $thisNamespaceParts = explode('\\', $this->namespace);
        $namespaceParts = explode('\\', $namespace);

        foreach ($thisNamespaceParts as $key => $part) {
            if ($part != $namespaceParts[$key]) {
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
        return $this->namespace . '\\' . implode('\\', $parts);
    }

    /**
     * Get the directory path
     * @param string $namespace
     * @return string
     * @throws BadNamespace
     */
    public function getDirectoryPathFromNamespace(string $namespace): string
    {
        // Check base of namespace same as this namespace
        if (!str_starts_with($namespace, $this->namespace)) {
            throw new BadNamespace("$namespace doesn't match this namespace ($this->namespace)");
        }

        // Return path
        return $this->path . str_replace('\\', '/', substr($namespace, strlen($this->namespace)));
    }

    /**
     * Get file path from full class name
     * @param string $classWithNamespace
     * @return string
     * @throws BadNamespace
     */
    public function getClassFilepath(string $classWithNamespace): string
    {
        return $this->getDirectoryPathFromNamespace($classWithNamespace) . '.php';
    }

}