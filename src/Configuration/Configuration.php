<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallClassManipulator\Configuration;

use Sebk\SmallClassManipulator\Configuration\Bean\NamespaceConfiguration;
use Sebk\SmallClassManipulator\Configuration\Bean\SelectorConfiguration;
use Sebk\SmallClassManipulator\Configuration\Exception\FileNotFoundException;
use Sebk\SmallClassManipulator\Configuration\Exception\MissingParameterException;
use Sebk\SmallClassManipulator\Configuration\Exception\WrongParameterTypeException;

class Configuration
{

    // Parameters
    const SELECTORS = 'selectors';
    const ROOT_DIR = 'root_dir';

    /** @var SelectorConfiguration[] */
    protected array $selectors = [];

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

}