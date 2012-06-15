<?php

/**
 * @see Ingot_JQuery_JqGrid_Column_Decorator_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Column/Decorator/Abstract.php';

/**
 * Decorate a column which contains HTML links
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Column_Decorator_Link extends Ingot_JQuery_JqGrid_Column_Decorator_Abstract
{
    protected $_options = array();

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct($column, $options = array())
    {
        $this->_column = $column;
        $this->_options = $options;
        
        $this->decorate();
    }

    /*
	 * Decorate column to display URL links
	 * 
	 * @return void
	 */
    public function decorate()
    {
        if (! isset($this->_options['link'])) {
            throw new Ingot_JQuery_JqGrid_Exception('A valid link must be supplied.');
        }
        
        if (! is_array($this->_options['column']) && isset($this->_options['column'])) {
            $this->_options['column'] = array(
                
                $this->_options['column']
            );
        }
    }

    /**
     * Build a link contain column values using a string composed of zero or more 
     * directives as per vsprintf().
     * 
     * Additional columns can be supplied, if the link needs to access different
     * column values.
     * 
     * @param array $row
     */
    public function cellValue($row)
    {
        
        // Count the number of arguments to be formatted
        $countArg = substr_count($this->_options['link'], '%');
        
        if ($countArg > 0) {
            // If no columns have been supplied, format link using current column names
            if (count($this->_options['column']) == 0) {
                $column = array_fill(1, $countArg, $row[$this->_column->getName()]);
            } else {
                // If columns have been defined, format link using user defined column names
                $column = array_intersect_key($row, array_flip($this->_options['column']));
            }
            
            $link = vsprintf($this->_options['link'], $column);
        }
        
        return "<a href=\"" . $link . "\">" . $row[$this->getName()] . "</a>";
    }

}