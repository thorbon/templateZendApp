<?php

/**
 * Plugin Abstract
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

abstract class Ingot_JQuery_JqGrid_Plugin_Abstract
{
    /**
     * Grid Instance
     * 
     * @var Ingot_JQuery_JqGrid
     */
    protected $_grid;
    
    /**
     * Grid Data Instance
     * 
     * @var object
     */
    protected $_gridData;
    
    /**
     * View Instance
     * 
     * @var Zend_View
     */
    protected $_view;

    /**
     * Set View Instance
     * 
     * @param $view
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Set Grid Instance 
     * 
     * @param $grid Ingot_JQuery_JqGrid
     * @return void
     */
    public function setGrid(Ingot_JQuery_JqGrid $grid)
    {
        $this->_grid = $grid;
    }

    /**
     * Get Grid Instance
     *
     * @return Ingot_JQuery_JqGrid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Set an instance of the grid data structure
     *
     * @param object $data
     * @return void
     */
    public function setGridData($data)
    {
        $this->_gridData = $data;
    }

    /**
     * Get an instance of the grid data structure
     * 
     * @return object
     */
    public function getGridData()
    {
        return $this->_gridData;
    }

    /**
     * Add HTML to plugin
     *
     * @param $html HTML string
     */
    public function addHtml($html)
    {
        $this->_view->jqGridPluginBroker['html'][] = $html;
    }

    /**
     * Add javascript to plugin for onload
     *
     * @param $js javascript string
     */
    public function addOnLoad($js)
    {
        $this->_view->jqGridPluginBroker['onload'][] = $js;
    }

    /**
     * Add javascript to plugin
     *
     * @param $js javascript string
     */
    public function addJavascript($js, $onload = false)
    {
        if ($onload == true) {
            return $this->addOnLoad($js);
        }
        
        $this->_view->jqGridPluginBroker['js'][] = $js;
    }

    abstract public function preResponse();
    abstract public function postResponse();
    abstract public function preRender();
    abstract public function postRender();
}