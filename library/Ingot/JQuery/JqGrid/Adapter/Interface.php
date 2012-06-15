<?php

/**
 * JqGrid Adapter Interface
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */
interface Ingot_JQuery_JqGrid_Adapter_Interface
{
    /**
     * Sort records
     *
     * @param string $field Field which will be sorted
     * @param string $direction Sort direction: 'ASC' or 'DESC'
     * @access public                          
     */
    public function sort($field, $direction);

    /**
     * Filter records
     *
     * @param string $field Field which will be searched
     * @access public $value Search value                
     * @param string $expression Type of search
     */
    public function filter($field, $value, $expression, $options = array());
}