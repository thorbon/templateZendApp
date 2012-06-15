<?php
  
class Web_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);
        $this->__loadModuleConfig();
        $this->__initAutoload();        
        $this->__initViewHelpers();
        $this->__initRegisterPlugins();
    }
    
    protected function __loadModuleConfig()
    {
        $configFile = APPLICATION_PATH . '/modules/' . strtolower($this->getModuleName()) . '/configs/UserConfig.ini';
        $gridConfigFile = APPLICATION_PATH . '/modules/' . strtolower($this->getModuleName()) . '/configs/grid.ini';
 
        if (!file_exists($configFile)) {
            return;
        }
        
        $config = new Zend_Config_Ini($configFile, $this->getEnvironment());
        $this->setOptions($config->toArray());
        Zend_Registry::set('User_Config', new Zend_Config($this->getOptions()));
        
        if (!file_exists($gridConfigFile)) {
            return;
        }
 
        $grid = new Zend_Config_Ini($gridConfigFile, 'production');
        $this->setOptions($grid->toArray());
        Zend_Registry::set('Grid_Config', new Zend_Config($this->getOptions()));
    }
    
    protected function __initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => dirname(__FILE__),
        ));
    }
    
    protected function __initView()
    {
        $view = new Zend_View($this->getOptions());
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        return $view;

    }
    
    protected function __initViewHelpers() {
        $view = new Zend_View();
        $view->headTitle('Ezmi')->setSeparator(' / ');
    }
    
    protected function __initRegisterPlugins()
    {
        $this->bootstrap('Frontcontroller')
                ->getResource('Frontcontroller')
                ->registerPlugin(new My_Controller_Plugin_Auth());
    }
}

