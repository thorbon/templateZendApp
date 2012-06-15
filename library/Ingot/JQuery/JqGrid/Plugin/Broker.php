<?php

/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Plugin Broker
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */

class Ingot_JQuery_JqGrid_Plugin_Broker extends Ingot_JQuery_JqGrid_Plugin_Abstract
{
    /**
     * Array of instance of objects extending Zend_Controller_Plugin_Abstract
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * View instance
     * 
     * @var Zend_View
     */
    protected $_view;

    /**
     * Set view instance
     * 
     * @param Zend_View $view
     */
    public function setView($view)
    {
        $this->_view = $view;
        
        $this->_view->jqGridPluginBroker = array();
        $this->_view->jqGridPluginBroker['html'] = array();
        $this->_view->jqGridPluginBroker['js'] = array();
        $this->_view->jqGridPluginBroker['onload'] = array();
    }

    /**
     * Register a plugin.
     *
     * @param  Ingot_JQuery_JqGrid_Plugin_Abstract $plugin
     * @return Ingot_JQuery_JqGrid_Plugin_Broker
     */
    public function registerPlugin(Ingot_JQuery_JqGrid_Plugin_Abstract $plugin)
    {
        $plugin->setGrid($this->_grid);
        $plugin->setGridData($this->_gridData);
        
        $this->_plugins[] = $plugin;
        
        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param  Ingot_JQuery_JqGrid_Plugin_Abstract $plugin
     * @return Ingot_JQuery_JqGrid_Plugin_Broker
     */
    public function unregisterPlugin($plugin)
    {
        if ($plugin instanceof Ingot_JQuery_JqGrid_Plugin_Abstract) {
            // Given a plugin object, find it in the array and unset
            foreach ($this->_plugins as $key => $_plugin) {
                if ($plugin === $_plugin) {
                    unset($this->_plugins[$key]);
                }
            }
        } elseif (is_string($plugin)) {
            // Given a plugin class, find all plugins of that class and unset them
            foreach ($this->_plugins as $key => $_plugin) {
                $type = get_class($_plugin);
                if ($plugin == $type) {
                    unset($this->_plugins[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Has plugin been registered
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class Class name of plugin(s)
     * @return false|Ingot_JQuery_JqGrid_Plugin_Abstract|array Returns false if none found, plugin if only one found, and array of plugins if multiple plugins of same class found
     */
    public function getPlugin($class)
    {
        $found = array();
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }
        
        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Set Grid
     *
     * @param Ingot_JQuery_JqGrid $grid
     * @return void
     */
    public function setGrid(Ingot_JQuery_JqGrid $grid)
    {
        $this->_grid = $grid;
        return $this;
    }

    /**
     * Set Grid Data
     *
     * @param object $data
     * @return void
     */
    public function setGridData($data)
    {
        $this->_gridData = $data;
    }

    /**
     * Called before Ingot_JQuery_JqGrid sends response 
     *
     * @return void
     */
    public function preResponse()
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->setGridData($this->_gridData);
            $plugin->setView($this->_view);
            $plugin->preResponse();
        }
    }

    /**
     * Called after Ingot_JQuery_JqGrid has generated response
     *
     * @return void
     */
    public function postResponse()
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->setGridData($this->_gridData);
            $plugin->setView($this->_view);
            $plugin->postResponse();
        }
    }

    /**
     * Called before Ingot_JQuery_JqGrid renders
     * 
     * This is the only plugin hook, which has no access to the 
     * grid data structure.
     *
     * @return void
     */
    public function preRender()
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->setView($this->_view);
            $plugin->preRender();
        }
    }

    /**
     * Called after Ingot_JQuery_JqGrid renders
     *
     * @return void
     */
    public function postRender()
    {
        foreach ($this->_plugins as $plugin) {
            $plugin->setGridData($this->_gridData);
            $plugin->setView($this->_view);
            $plugin->postRender();
        }
    }
}