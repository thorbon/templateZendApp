<?php

/**
 * @see Zend_Paginator_Adapter_Array
 */
require_once 'Zend/Paginator/Adapter/Array.php';

/**
 * @see Ingot_JQuery_JqGrid_Adapter_Interface
 */
require_once 'Ingot/JQuery/JqGrid/Adapter/Interface.php';

/**
 * JqGrid Array Adapter
 * 
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Adapter_Array extends Zend_Paginator_Adapter_Array implements Ingot_JQuery_JqGrid_Adapter_Interface
{
    /**
     * Sort Array
     * 
     * @param string $field
     * @param string $direction
     */
    public function sort($field, $direction)
    {
        $sort = array();
        $count = count($this->_array);
        
        for ($i = 0; $i < $count; $i ++) {
            $item = (array) $this->_array[$i];
            $sort[$i] = $item[$field];
        }
        
        array_multisort($sort, ($direction == 'desc') ? SORT_DESC : SORT_ASC, $this->_array);
    }

    /**
     * Filter Array
     * 
     * @param string $field
     * @param string $value
     * @param string $operator
     */
    public function filter($field, $value, $expression, $options = array())
    {
        if (isset($options['multiple'])) {
            return $this->_multiFilter(array(
                
                'field' => $field , 
                'value' => $value , 
                'expression' => $expression
            ), $options);
        }
        
        return $this->_singleFilter($field, $value, $expression);
    }

    /**
     * Multiple Field Filter 
     * 
     * @param $rules
     * @param $options
     */
    private function _multiFilter($rules, $options = array())
    {
        $booleanTable = array();
        
        // Evaluate each filter rule
        foreach ($this->_array as $row => $item) {
            foreach ($rules['field'] as $key => $field) {
                if (array_key_exists($field, $item)) {
                    $booleanTable[$row][$field] = $this->_compare(strtolower($item[$field]), strtolower($rules['value'][$key]), $rules['expression'][$key]);
                }
            }
        }
        
        // Apply filter logic to remove fields based on a boolean comparsion
        foreach ($booleanTable as $row => $booleans) {
            switch ($options['boolean']) {
                case 'OR':
                    if (array_sum($booleans) == 0) {
                        unset($this->_array[$row]);
                    }
                    break;
                
                case 'AND':
                default:
                    if (array_sum($booleans) < count($booleans)) {
                        unset($this->_array[$row]);
                    }
                    break;
            }
        }
    }

    /**
     * Single Field Filter
     * 
     * @param $field
     * @param $value
     * @param $operator
     */
    private function _singleFilter($field, $value, $expression)
    {
        foreach ($this->_array as $row => $item) {
            if ($this->_compare(strtolower($item[$field]), strtolower($value), $expression) != true) {
                unset($this->_array[$row]);
            }
        }
    }

    /**
     * Compare two values
     *
     * @param mixed $a
     * @param mixed $b
     * @param string $expression
     * @return boolean
     */
    private function _compare($a, $b, $expression)
    {
        switch ($expression) {
            case "BEGIN_WITH":
            case "NOT_BEGIN_WITH":
                $bool = strpos($a, $b) === 0;
                return ($expression == 'BEGIN_WITH') ? $bool : ! $bool;
                break;
            
            case "IN":
            case "NOT_IN":
                $bool = in_array($a, (array) $b);
                return ($expression == 'IN') ? $bool : ! $bool;
            
            case "END_WITH":
            case "NOT_END_WITH":
                $bool = substr_compare($a, $b, strlen($a) - strlen($b), strlen($b)) == 0;
                return ($expression == 'END_WITH') ? $bool : ! $bool;
                break;
            
            case "CONTAIN":
            case "NOT_CONTAIN":
                $bool = strpos($a, $b) > - 1;
                return ($expression == 'CONTAIN') ? $bool : ! $bool;
                break;
            
            case "EQUAL":
                return $a === $b;
                break;
            
            case "NOT_EQUAL":
                return $a !== $b;
                break;
            
            case "LESS_THAN":
                return $a < $b;
                break;
            
            case "LESS_THAN_OR_EQUAL":
                return $a <= $b;
                break;
            
            case "GREATHER_THAN":
                return $a > $b;
                break;
            
            case "GREATER_THAN_OR_EQUAL":
                return $a >= $b;
                break;
            
            default:
                return true;
                break;
        }
    }
}