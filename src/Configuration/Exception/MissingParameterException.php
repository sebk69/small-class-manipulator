<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\Configuration\Exception;

class MissingParameterException extends ConfigurationException
{

    public function __construct(string $parameter = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('Missing parameter : ' . $parameter, $code, $previous);
    }

}