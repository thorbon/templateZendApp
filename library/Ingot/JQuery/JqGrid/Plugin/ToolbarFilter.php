<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Display a search filter on each column
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_ToolbarFilter extends Ingot_JQuery_JqGrid_Plugin_Abstract
{
    protected $_options;

    public function __construct($options = array())
    {
        $this->_options = $options;
    }

    public function preRender()
    {
        
        if (! isset($this->_options['stringResult'])) {
            $this->_options['stringResult'] = true;
        }
        
        $js = sprintf('%s("#%s").filterToolbar(%s);', 
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(), 
                $this->getGrid()->getId(), 
                ZendX_JQuery::encodeJson($this->_options));
        
        $this->addOnLoad($js);
        
        $columns = $this->getGrid()->getColumns();
        
        foreach ($columns as $column) {
            $column->setOption('search', true);
        }
    }

    public function postRender()
    {    // Not implemented
    }

    public function preResponse()
    {    // Not implemented
    }

    public function postResponse()
    {    // Not implemented
    }
}