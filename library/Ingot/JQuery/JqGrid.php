<?php
/**
 * This grid component allows the rendering of jqGrid which is an ajax-enabled 
 * javascript control that provides solutions for representing and manipulating 
 * tabular data on the web. 
 * 
 * jqGrid uses the jQuery javascript library. 
 *
 * @see http://www.trirand.com/blog/
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */
class Ingot_JQuery_JqGrid
{
    /**
     * The unique id assigned to the grid
     * 
     * @var string
     */
    protected $_id = null;

    /**
     * An array of grid options
     * 
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options
     * @var array
     */
    protected $_options = array();

    /**
     * An array of grid columns objects
     * 
     * @var array
     */
    protected $_columns = array();

    /**
     * An adaptor for accessing a data source directly, making use 
     * of Zend_Paginator for pagination.
     *
     * @var Zend_Paginator_Adapter_Interface
     * @var Ingot_JQuery_JqGrid_Adaptor_Interface
     */
    protected $_adapter = null;

    /**
     * The provided view
     * 
     * @var Zend_View_Interface
     */
    protected $_view = null;

    /**
     * Pagination object
     * 
     * @var Zend_Paginator
     */
    protected $_paginator = null;

    /**
     * Default item count per page
     * 
     * @var int
     */
    protected $_defaultItemCountPerPage = 20;

    /**
     * The shorthand search operator provided by jqgrid will be 
     * converted to a more readable format, to provide a standard
     * expression set for use within the adapters.
     * 
     * @var string
     */
    protected $_expression = array(
        'eq' => 'EQUAL' , 
        'ne' => 'NOT_EQUAL' , 
        'lt' => 'LESS_THAN' , 
        'le' => 'LESS_THAN_OR_EQUAL' , 
        'gt' => 'GREATER_THAN' , 
        'ge' => 'GREATER_THAN_OR_EQUAL' , 
        'bw' => 'BEGIN_WITH' , 
        'bn' => 'NOT_BEGIN_WITH' , 'in' => 'IN' , 
        'ni' => 'NOT_IN' , 'ew' => 'END_WITH' , 
        'en' => 'NOT_END_WITH' , 
        'cn' => 'CONTAIN' , 
        'nc' => 'NOT_CONTAIN'
    );

    /**
     * Instance of Ingot_JQuery_JqGrid_Plugin_Broker
     * 
     * @var unknown_type
     */
    protected $_plugins;

    /**
     * Constructor.
     *
     * @param Ingot_JQuery_JqGrid_Adapter_Interface $adaptor
     * @param array $options
     */
    public function __construct($id, $adapter, array $options = array())
    {
        Zend_Paginator::addAdapterPrefixPath('Ingot_JQuery_JqGrid_Adapter', 'Ingot/JQuery/JqGrid/Adapter');
        
        if ($adapter instanceof Ingot_JQuery_JqGrid_Adapter_Interface) {
            $this->_adapter = $adapter;
        } else {
            throw new Ingot_JQuery_JqGrid_Exception('Ingot_JQuery_JqGrid only accepts instances of the type ' . 'Ingot_JQuery_JqGrid_Adapter_Interface');
        }
        $this->_id = $id;
        
        // Set grid options, automatically set the default options
        // and over-ride with user options.
        $this->_setDefaultOptions();
        
        if (isset($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            if (! is_array($options)) {
                throw new Zend_Exception('JqGrid options must be in an array or a Zend_Config object');
            }
            $this->setOptions($options);
        }
        
        $this->_plugins = new Ingot_JQuery_JqGrid_Plugin_Broker();
        $this->_plugins->setGrid($this);
        
        $this->_plugins->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_Pager());
    }

    /**
     * Get unique grid identifier
     * 
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set default grid options
     * 
     * @return void
     */
    protected function _setDefaultOptions()
    {
        $this->_options['url'] = '';
        $this->_options['datatype'] = 'json';
        $this->_options['mtype'] = 'POST';
        $this->_options['viewrecords'] = true;
        $this->_options['colModel'] = array();
        $this->_options['autowidth'] = true;
        $this->_options['height'] = '400px';
        $this->_options['rowNum'] = $this->_defaultItemCountPerPage;
        $this->_options['rowList'] = range($this->_defaultItemCountPerPage, $this->_defaultItemCountPerPage * 5, $this->_defaultItemCountPerPage);
        $this->_options['caption'] = ucwords(str_replace("_", "", $this->_id));
        $this->_options['postData'] = array(
            
            'grid' => $this->_id
        );
    }

    /**
     * Override set to allow access to grid options
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setOption($name, $value);
    }

    /**
     * Override get to allow access to grid options
     * 
     * @param string $name Grid option name
     * @return void
     */
    public function __get($name)
    {
        return $this->getOption($name);
    }

    /**
     * Set a single grid option
     * 
     * @return Ingot_JQuery_JqGrid
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;
        return $this;
    }

    /**
     * Sets grid options
     *
     * @param array $options
     * @return Ingot_JQuery_JqGrid
     */
    public function setOptions(array $options = array())
    {
        foreach ($options as $k => $v) {
            $this->setOption($k, $v);
        }
        return $this;
    }

    /**
     * Get a single grid option
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

    /**
     * Get all grid options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the view object.
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Paginator
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieves the view instance.  If none registered, attempts to pull 
     * from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if ($this->_view === null) {
            /**
             * @see Zend_Controller_Action_HelperBroker
             */
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if ($viewRenderer->view === null) {
                $viewRenderer->initView();
            }
            $this->_view = $viewRenderer->view;
        }
        return $this->_view;
    }

    /**
     * Add a column object to the grid.
     * 
     * @param Ingot_JQuery_JqGrid_Column $column
     * @return Ingot_JQuery_JqGrid
     */
    public function addColumn($column)
    {
        $this->_columns[$column->getName()] = $column;
        return $column;
    }

    /**
     * Return all columns
     * 
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Render the grid.
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }
        
        $view = $this->getView();
        $view->addHelperPath("Ingot/JQuery/JqGrid/View/Helper", "Ingot_JQuery_JqGrid_View_Helper");
        
        $this->_plugins->setView($view);
        $this->_plugins->preRender();
        
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        // Check if a remote URL has been set, if not set to current controller
        if (empty($this->_options['url'])) {
            $this->_options['url'] = $request->getRequestUri();
            
            // Automatically send a response to update jqGrid
            if ($request->isXmlHttpRequest() && $request->getParam('grid') == $this->_id) {
                $this->sendResponse($request);
            }
        }
        
        foreach ($this->_columns as $column) {
            $this->_options['colModel'][] = $column->getOptions();
        }
        
        $this->_plugins->postRender();
        
        return $view->jqGrid($this);
    }

    /**
     * Send Response
     * 
     * @param $request Zend_Controler_Request_Abstract
     * @return string
     */
    protected function sendResponse(Zend_Controller_Request_Abstract $request)
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender();
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
        
        echo $this->response($request);
        $this->_plugins->postResponse();
    }

    /**
     * Return a data type which describe's the jqGrid structure, currently only 
     * JSON is supported.
     * 
     * @todo Allow a factory to return different data types ie: XML.
     * @return string
     */
    public function response(Zend_Controller_Request_Abstract $request)
    {
        $data = $this->_createGridData($request);
        
        $this->_plugins->setGridData($data);
        $this->_plugins->preResponse();
        
        return ZendX_JQuery::encodeJson($data);
    }

    /**
     * Register Plugin
     * 
     * @return void;
     */
    public function registerPlugin($plugin)
    {
        return $this->_plugins->registerPlugin($plugin);
    }

    /**
     * Has plugin already been registered?
     * 
     * @param $plugin
     * @return boolean
     */
    public function hasPlugin($plugin)
    {
        return $this->_plugins->hasPlugin($plugin);
    }

    /**
     * Unregister Plugin
     * 
     * @return void;
     */
    public function unregisterPlugin($plugin)
    {
        return $this->_plugins->unregisterPlugin($plugin);
    }

    /**
     * Create the grid data structure
     * 
     * @return object
     */
    protected function _createGridData(Zend_Controller_Request_Abstract $request)
    {
        // Instantiate Zend_Paginator with the required data source adaptor
        if (! $this->_paginator instanceof Zend_Paginator) {
            $this->_paginator = new Zend_Paginator($this->_adapter);
            $this->_paginator->setDefaultItemCountPerPage($request->getParam('rows', $this->_defaultItemCountPerPage));
        }
        
        // Filter items by supplied search criteria
        if ($request->getParam('_search') == 'true') {
            $filter = $this->_getFilterParams($request);
            $this->_paginator->getAdapter()->filter($filter['field'], $filter['value'], $filter['expression'], $filter['options']);
        }
        
        // Sort items by the supplied column field
        if ($request->getParam('sidx')) {
            $this->_paginator->getAdapter()->sort($request->getParam('sidx'), $request->getParam('sord', 'asc'));
        }
        
        // Pass the current page number to paginator
        $this->_paginator->setCurrentPageNumber($request->getParam('page', 1));
        
        // Fetch a row of items from the adapter
        $rows = $this->_paginator->getCurrentItems();
        
        $grid = new stdClass();
        $grid->page = $this->_paginator->getCurrentPageNumber();
        $grid->total = $this->_paginator->getItemCountPerPage();
        $grid->records = $this->_paginator->getTotalItemCount();
        $grid->rows = array();
        
        foreach ($rows as $k => $row) {
            
            if (isset($row['id'])) {
                $grid->rows[$k]['id'] = $row['id'];
            }
            
            $grid->rows[$k]['cell'] = array();
            
            foreach ($this->_columns as $column) {
                array_push($grid->rows[$k]['cell'], $column->cellValue($row));
            }
        }
        return $grid;
    }

    /**
     * Return filter parameters for single or multiple fields.
     *
     * @param Zend_Request $request
     */
    private function _getFilterParams($request)
    {
        
        $filters = array();
        
        // Multiple field filtering
        if ($request->getParam('filters')) {
            $filter = Zend_Json::decode($request->getParam('filters'));
            
            if (count($filter['rules']) > 0) {
                foreach ($filter['rules'] as $rule) {
                    $filters['field'][] = $rule['field'];
                    $filters['value'][] = $rule['data'];
                    $filters['expression'][] = $this->_expression[$rule['op']];
                }
                
                $filters['options']['multiple'] = true;
                $filters['options']['boolean'] = (isset($json['groupOp'])) ? $json['groupOp'] : 'AND';
                return $filters;
            }
        }
        
        // Single field filtering
        return array(
            
            'field' => $request->getParam('searchField') , 
            'value' => trim($request->getParam('searchString')) , 
            'expression' => $this->_expression[$request->getParam('searchOper', 'eq')] , 
            'options' => array()
        );
    }
}