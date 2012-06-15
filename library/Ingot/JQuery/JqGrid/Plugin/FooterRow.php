<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Display a footer row on grid, which can aggregate column values
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_FooterRow extends Ingot_JQuery_JqGrid_Plugin_Abstract
{

    protected $_columns = array();
    protected $_userData = array();
    
    const SUM = 'sum';
    const COUNT = 'count';
    const AVERAGE = 'avg';
    const AVG = 'avg';
    const MINIMUM = 'min';
    const MIN = 'min';
    const MAXIMUM = 'max';
    const MAX = 'max';

    public function preRender()
    {
        $this->_grid->setOption('footerrow', true);
        $this->_grid->setOption('userDataOnFooter', true);
    }

    public function postRender()
    {    // Not implemented
    }

    public function preResponse()
    {
        $columns = array_values($this->_grid->getColumns());
        
        if (count($this->_columns) > 0) {
            foreach ($this->_gridData->rows as $row) {
                foreach ($row['cell'] as $k => $cell) {
                    
                    $columnName = $columns[$k]->getName();
                    
                    if (array_key_exists($columnName, $this->_columns)) {
                        $this->_createSummaryRow($this->_columns[$columnName], $cell);
                    }
                }
            }
            
            // Add summary row
            foreach ($columns as $column) {
                $columnName = $column->getName();
                
                if (array_key_exists($columnName, $this->_columns)) {
                    $value = number_format($this->_userData[$columnName], $this->_columns[$columnName]['decimals']);
                    
                    // Add summary row prefix
                    if (isset($this->_columns[$columnName]['prefix'])) {
                        $value = $this->_columns[$columnName]['prefix'] . $value;
                    }
                    
                    if (isset($this->_columns[$columnName]['suffix'])) {
                        $value = $value . $this->_columns[$columnName]['suffix'];
                    }
                    
                    $this->_gridData->userdata[$columnName] = $value;
                }
            }
        }
    }

    public function postResponse()
    {    // Not implemented
    }

    /**
     * Add column
     * 
     * @param unknown_type $column
     * @param unknown_type $aggregate
     */
    public function addLabel($column, $value)
    {
        
        if ($column instanceof Ingot_JQuery_JqGrid_Column) {
            $column = $column->getName();
        }
        
        $this->_columns[$column]['name'] = $column;
        $this->_columns[$column]['label'] = $value;
    }

    /**
     * Add column aggregate
     * 
     * @param mixed $column Name of column
     * @param mixed $aggregate Aggregate operator
     * @param array $options Array of options
     */
    public function addAggregate($column, $aggregate, $options = array())
    {
        
        if ($column instanceof Ingot_JQuery_JqGrid_Column) {
            $column = $column->getName();
        }
        
        $this->_columns[$column]['name'] = $column;
        $this->_columns[$column]['aggregate'] = $aggregate;
        
        if (! isset($options['decimals'])) {
            $this->_columns[$column]['decimals'] = 0;
        }
        
        $this->_columns[$column] = array_merge($this->_columns[$column], $options);
    }

    /**
     * Perform aggregate functions on a specfic column
     * 
     * @param string $name Column name
     * @param string $aggregate Aggregate Operator
     * @param string $value Column value
     */
    protected function _createSummaryRow($name, $aggregate, $value)
    {
        if (isset($aggregate)) {
            switch (strtoupper($aggregate)) {
                case 'SUM':
                    $this->_userData[$name] += $value;
                    break;
                
                case 'COUNT':
                    $this->_userData[$name] += 1;
                    break;
                
                case 'MIN':
                    $this->_columns[$name][$aggregate][] = $value;
                    $this->_userData[$name] = min($this->_columns[$name][$aggregate]);
                    break;
                
                case 'MAX':
                    $this->_columns[$name][$aggregate][] = $value;
                    $this->_userData[$name] = max($this->_columns[$name][$aggregate]);
                    break;
                
                case 'AVG':
                    $this->_columns[$name][$aggregate][] = $value;
                    $this->_userData[$name] = array_sum($this->_columns[$name][$aggregate]) / count($this->_columns[$name][$aggregate]);
                    break;
                
                default:
                    $this->_userData[$name] = $column['label'];
                    break;
            }
        }
    }
}