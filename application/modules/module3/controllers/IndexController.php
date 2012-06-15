<?php

class Module3_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        
        $this->logger = Zend_Registry::get('Zend_Log');
        
        // setting the site in the title; possibly in the layout script:
        $this->view->headTitle('Module 1');
        
        // setting the controller and action name as title segments:
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->view->headTitle($request->getControllerName())
             ->headTitle($request->getActionName());
         
        // setting a separator string for segments:
        $this->view->headTitle()->setSeparator(' / ');
    }

    public function indexAction()
    {        
        $this->view->message = "This is the INDEX Action within the INDEX Controller";
    }
}

