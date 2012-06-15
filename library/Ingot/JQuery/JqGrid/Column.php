<?php

/**
 * Represents a single column within the grid. 
 *
 * @package Ingot_JQuery_JQgrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Column
{
    /**
     * Field name of column
     * 
     * @var string
     */
    protected $_name = null;

    /**
     * An array of column properites
     * 
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
     * @var array
     */
    
    protected $_options = array();

    /**
     * An array of row on values
     * 
     * @var unknown_type
     */
    protected $_row = array();

    /**
     * Constructer.
     * 
     * @param string $name Column field name
     * @param array $options
     */
    public function __construct($name, $options = array())
    {
        
        $this->_name = $name;
        $this->_options = $options;
        $this->_options['name'] = $this->_name;
        if (empty ( $this->_options ['label'] )) {
			$this->_options ['label'] = ucwords ( str_replace ( "_", " ", $this->_name ) );
		}
}

    /*
	 * Get the column field name
	 * 
	 * @return string
	 */
    public function getName()
    {
        return $this->_name;
    }

    /*
	 * Set the column friendly label name
	 * 
	 * @return string
	 */
    
    public function setLabel($label)
    {
        $this->_options['label'] = $label;
    }

    /**
     * Override set to allow access to column options
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setOption($name, $value);
    }

    /**
     * Override get to allow access to column options
     * 
     * @param string $name column option name
     * @return void
     */
    public function __get($name)
    {
        return $this->getOption($name);
    }

    /*
     * Get a single column option
     * 
     * @return mixed
     */
    public function getOption($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } else {
            return false;
        }
    }

    /*
     * Set a single column option
     * 
     * @return Ingot_JQuery_JqGrid_Column
     */
    public function setOption($name, $value)
    {
        
        if ($name == 'name' && isset($this->_options['name'])) {
            throw new Ingot_JQuery_JqGrid_Exception('The column name cannot be changed, as it has already been defined.');
        }
        
        $this->_options[$name] = $value;
        return $this;
    }

    /*
     * Get all column options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get cell value
     * 
     * Accepts an array representing a grid row, useful 
     * for decorators which may require access to other
     * cells in row data.
     * 
     * @param $row Row array containing column cell value
     * @return mixed
     */
    public function cellValue($row)
    {
        return htmlentities($row[$this->getName()]);
    }
}