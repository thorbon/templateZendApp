<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        $this->__initConfig();
        $this->__initDatabase();
        $this->__initRegisterLogger();
        $this->__initView();
        $this->__initViewHelpers();
        $this->__initSession();
    }
    
    protected function __initConfig()
    {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
    }
    
    protected function __initDatabase()
    {
        $resource = $this->getPluginResource('multidb');
        $resource->init();
        $web_db = $resource->getDb('zendapp_web');
        $module3_db = $resource->getDb('zendapp_module3');
        
        if ($this->getEnvironment() != 'production') {
            // Instantiate the profiler in your bootstrap file 
            $profiler = new Zend_Db_Profiler_Firebug('All Database Queries:');
            // Enable it
            $profiler->setEnabled(true);
            // Attach the profiler to your db adapter 
            $web_db->setProfiler($profiler);
            $module3_db->setProfiler($profiler);
        }
        
        Zend_Registry::set('Zend_Web_Db', $web_db);
        Zend_Registry::set('Zend_Module3_Db', $module3_db);
    }
    
    protected function __initRegisterLogger() {
        $this->bootstrap('log');

        if (!$this->hasPluginResource('log')) {
            throw new Zend_Exception('Log not enabled in application.ini');
        }

        $logger = $this->getResource('log');
        assert($logger != null);
        
        if ($this->getEnvironment() != 'production') {
            $writer = new Zend_Log_Writer_Firebug();
        }
        
        Zend_Registry::set('Zend_Log', $logger);
    }
    
    protected function __initViewHelpers() {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $view->addHelperPath("Zend/Dojo/View/Helper", "Zend_Dojo_View_Helper");
    }
    
    protected function __initView()
    {
       $view = new Zend_View();
       return $view;
    }
    
    protected function __initSession()
    {
        $namespace = 'default';        
        $session = new Zend_Session_Namespace('Zend_Auth');

        if (!isset($session->initialized))
        {
            Zend_Session::regenerateId();

            $session->initialized = true;
            $session->startId = time();
            $session->timeout = 1800;
            $session->setExpirationSeconds($session->timeout);                            
        }
        
        Zend_Registry::set('Zend_Session', $session);       
    }
}

