<?php

declare(strict_types=1);

class BuilderFactory
{
    private $class;

    public function __construct($class)
    {
        $this->class=$class;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getBuilderInstance()
    {
        $class_name = ucfirst($this->class) . 'Builder';
        require_once(_DEALT_MODULE_BUILDERS_DIR_ . $class_name.'.php');
        if (!class_exists($class_name)) {
            throw new LogicException("Unable to load class: $class_name");
        }
        return new $class_name();
    }
}