<?php
  
class Module3_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __construct($application)
    {
        $this->__initAutoload();
        //$this->__initViewHelpers();
    }
    
    protected function __initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => dirname(__FILE__),
        ));
        
        $autoloader->addResourceType('plugin', 'plugins/', 'Plugin');
    }
    
    protected function __initViewHelpers() {
        $view = new Zend_View();
        $view->headTitle('Zend App - API Manager')->setSeparator(' - ');
        //$view->headTitle()->append('API Manager');
    }

}

