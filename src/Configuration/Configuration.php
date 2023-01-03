<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\Configuration;

use SmallClassManipulator\Configuration\Bean\SelectorConfiguration;
use SmallClassManipulator\Configuration\Exception\FileNotFoundException;
use SmallClassManipulator\Configuration\Exception\MissingParameterException;
use SmallClassManipulator\Configuration\Exception\SelectorNotFoundException;
use SmallClassManipulator\Configuration\Exception\WrongParameterTypeException;

class Configuration
{

    // Parameters
    const SELECTORS = 'selectors';
    const ROOT_DIR = 'rootDir';

    /** @var SelectorConfiguration[] */
    protected array $selectors = [];

    protected string $rootDir;

    /**
     * @param array $config
     * @throws Exception\SyntaxErrorException
     * @throws FileNotFoundException
     * @throws MissingParameterException
     * @throws WrongParameterTypeException
     */
    public function __construct(array $config)
    {

        // Check root dir exists
        if (!array_key_exists(static::ROOT_DIR, $config)) {
            throw new MissingParameterException(static::ROOT_DIR);
        }

        // Check root dir is directory
        if (!is_dir($config[static::ROOT_DIR])) {
            throw new FileNotFoundException('Base path (' . $config[static::ROOT_DIR] . ') is not a directory');
        }

        $this->rootDir = $config[static::ROOT_DIR];

        // Check public selectors exists
        if (!array_key_exists(static::SELECTORS, $config)) {
            throw new MissingParameterException(static::SELECTORS);
        }

        // Check selector is an array
        if (!is_array($config[static::SELECTORS])) {
            throw new WrongParameterTypeException(static::SELECTORS);
        }

        // Create selectors
        foreach ($config[static::SELECTORS] as $selectorKey => $selectorNamespaceConfig) {
            $this->selectors[$selectorKey] = new SelectorConfiguration($config[static::ROOT_DIR], $selectorNamespaceConfig);
        }

    }

    /**
     * Get root directory
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     *
     * @param $key
     * @return SelectorConfiguration
     * @throws SelectorNotFoundException
     */
    public function getSelector($key): SelectorConfiguration
    {
        if (!array_key_exists($key, $this->selectors)) {
            throw new SelectorNotFoundException('Selector not found (' . $key . ')');
        }

        return $this->selectors[$key];
    }

}