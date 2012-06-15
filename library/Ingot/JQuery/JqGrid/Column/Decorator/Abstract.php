<?php

/**
 * JqGrid Column Decorator Abstract
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

abstract class Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    /**
     * Column Instance
     * 
     * @var $_column Ingot_JQuery_JqGrid_Column
     */
    protected $_column;

    public function __construct(Ingot_JQuery_JqGrid_Column $column)
    {
        $this->_column = $column;
    }

    /**
	 * Get the column field name
	 * 
	 * @return string
	 */
    public function getName()
    {
        return $this->_column->getName();
    }

    /**
     * Override set to allow access to column options
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_column->setOption($name, $value);
    }

    /**
     * Override get to allow access to column options
     * 
     * @param string $name column option name
     * @return void
     */
    public function __get($name)
    {
        return $this->_column->getOption($name);
    }

    /**
     * Get a single column option
     * 
     * @return mixed
     */
    public function getOption($name)
    {
        $this->_column->getOption($name);
    }

    /**
     * Set a single column option
     * 
     * @return Ingot_JQuery_JqGrid_Column
     */
    public function setOption($name, $value)
    {
        $this->_column->getOption($name, $value);
    }

    /**
     * Get all column options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_column->getOptions();
    }

    /**
     * Get value of the column cell
     * 
     * @param $row Row array containing cell value
     * @return mixed
     */
    public function cellValue($row)
    {
        return $this->_column->cellValue($row);
    }

    abstract public function decorate();
} 