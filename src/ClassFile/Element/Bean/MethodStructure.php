<?php
/*
 * This file is a part of small-class-manipulator
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallClassManipulator\ClassFile\Element\Bean;

use SmallClassManipulator\ClassFile\Element\Enum\ClassScope;
use SmallClassManipulator\ClassFile\Element\MethodElement;
use SmallClassManipulator\ClassFile\Element\Trait\ClassScoped;
use SmallClassManipulator\ClassFile\Exception\AlreadyExistsException;
use SmallClassManipulator\ClassFile\Exception\NotFoundException;

class MethodStructure
{

    use ClassScoped;

    public function __construct(
        protected string $name,
        protected array $parameters = [],
        protected string $content = '',
        ClassScope|null $scope = ClassScope::public,
        bool $isStatic = false,
        protected string|null $returnType = null,
    ) {
        $this->setScope($scope);
        $this->setStatic($isStatic);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MethodStructure
     */
    public function setName(string $name): MethodStructure
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return TypedVarStructure[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return MethodStructure
     */
    public function setContent(string $content): MethodStructure
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    /**
     * @param string|null $returnType
     * @return MethodStructure
     */
    public function setReturnType(?string $returnType): MethodStructure
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * @param TypedVarStructure $parameter
     * @return MethodElement
     * @throws AlreadyExistsException
     * @throws AlreadyExistsException
     */
    public function addParameter(TypedVarStructure $parameter): static
    {
        if (array_key_exists($parameter->getName(), $this->parameters)) {
            throw new AlreadyExistsException('Can\'t add parameter \'' . $parameter->getName() . '\' : it aleady exists');
        }

        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $silentFail
     * @return $this
     * @throws NotFoundException
     */
    public function removeParameter(string $name, bool $silentFail = true): static
    {
        if (!array_key_exists($name, $this->parameters)) {
            if (!$silentFail) {
                throw new NotFoundException('Can\'t find parameter \'' . $name . '\' : not found');
            }
            return $this;
        }

        unset($this->parameters[$name]);

        return $this;
    }


}