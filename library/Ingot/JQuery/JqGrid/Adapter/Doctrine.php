<?php

/**
 * @see Zend_Paginator_Adapter_DbSelect
 */
require_once 'ZendX/Doctrine/Paginator/Adapter/Doctrine.php';

/**
 * @see Ingot_JQuery_JqGrid_Adapter_Interface
 */
require_once 'Ingot/JQuery/JqGrid/Adapter/Interface.php';

/**
 * JqGrid Doctrine Adapter
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author andy.roberts
 */

class Ingot_JQuery_JqGrid_Adapter_Doctrine extends ZendX_Doctrine_Paginator_Adapter_Doctrine implements Ingot_JQuery_JqGrid_Adapter_Interface
{
    protected $_operator = array(
        'EQUAL' => '= ?' , 
        'NOT_EQUAL' => '!= ?' , 
        'LESS_THAN' => '< ?' , 
        'LESS_THAN_OR_EQUAL' => '<= ?' , 
        'GREATER_THAN' => '> ?' , 
        'GREATER_THAN_OR_EQUAL' => '>= ?' , 
        'BEGIN_WITH' => 'LIKE ?' , 
        'NOT_BEGIN_WITH' => 'NOT LIKE ?' , 
        'END_WITH' => 'LIKE ?' , 
        'NOT_END_WITH' => 'NOT LIKE ?' , 
        'CONTAIN' => 'LIKE ?' , 
        'NOT_CONTAIN' => 'NOT LIKE ?'
    );

    /**
     * Sort the result set by a specified column.
     *
     * @param string $field Column name
     * @param string $direction Ascending (ASC) or Descending (DESC)
     * @return void
     */
    public function sort($field, $direction)
    {
        if (isset($field)) {
            $this->_query->orderBy($field . ' ' . $direction);
        }
    }

    /**
     * Filter the result set based on criteria.
     *
     * @param string $field Column name
     * @param string $value Value to filter result set
     * @param string $operation Search operator
     */
    public function filter($field, $value, $expression, $options = array())
    {
        
        if (! array_key_exists($expression, $this->_operator)) {
            return;
        }
        
        if (isset($options['multiple'])) {
            return $this->_multiFilter(array(
                
                'field' => $field , 
                'value' => $value , 
                'expression' => $expression
            ), $options);
        }
        
        return $this->_query->addWhere($field . ' ' . $this->_operator[$expression], $this->_setWildCardInValue($expression, $value));
    }

    /**
     * Multiple filtering
     * 
     * @return
     */
    private function _multiFilter($rules, $options = array())
    {
        
        $boolean = strtoupper($options['boolean']);
        
        foreach ($rules as $rule) {
            if ($boolean == 'OR') {
                $this->_query->orWhere($rule['field'] . ' ' . $this->_operator[$rule['expression']], $this->_setWildCardInValue($rule['expression'], $value));
            } else {
                $this->_query->addWhere($rule['field'] . ' ' . $this->_operator[$rule['expression']], $this->_setWildCardInValue($rule['expression'], $value));
            }
        }
    }

    /**
     * Place wildcard filtering in value
     *
     * @return string
     */
    private function _setWildCardInValue($expression, $value)
    {
        switch (strtoupper($expression)) {
            case 'BEGIN_WITH':
            case 'NOT_BEGIN_WITH':
                $value = $value . '%';
                break;
            
            case 'END_WITH':
            case 'NOT_END_WITH':
                $value = '%' . $value;
                break;
            
            case 'CONTAIN':
            case 'NOT_CONTAIN':
                $value = '%' . $value . '%';
                break;
        }
        
        return $value;
    }
}