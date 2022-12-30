<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Configuration\Bean;

use Sebk\SmallClassManipulator\Configuration\Exception\NotMatchException;

class SelectorConfiguration implements \ArrayAccess
{

    /** @var NamespaceConfiguration[] */
    protected array $namespaces = [];

    /**
     * @param array $config
     * @throws \Sebk\SmallClassManipulator\Configuration\Exception\SyntaxErrorException
     */
    public function __construct(string $rootDir, array $config)
    {
        foreach ($config as $name => $namespaceConfig)
        {
            $this->namespaces[$name] = new NamespaceConfiguration($namespaceConfig['namespace'], $rootDir . '/' .$namespaceConfig['path']);
        }
    }

    /**
     * Namespace offset exists
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->namespaces);
    }

    /**
     * Can't set namespace
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws \Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \Exception('Can\'t update namespace selector after initialization');
    }

    /**
     * Get a selector
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): NamespaceConfiguration
    {
        return $this->namespaces[$offset];
    }

    /**
     * Can't remove selector
     * @param mixed $offset
     * @return void
     * @throws \Exception
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \Exception('Can\'t remove namespace of selector after initialization');
    }

    /**
     * Get file path for a namespace
     * @param $fullClassname
     * @return string
     * @throws NotMatchException
     * @throws \Sebk\SmallClassManipulator\Configuration\Exception\BadNamespace
     */
    public function getClassFile($fullClassname)
    {
        foreach ($this->namespaces as $namespace) {
            if ($namespace->isSubNamespace($fullClassname)) {
                return $namespace->getClassFilepath($fullClassname);
            }
        }

        throw new NotMatchException('The class ' . $fullClassname . ' not match with that selector');
    }
    
}