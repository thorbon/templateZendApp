<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $this->view->assign('pageTitle', 'Ezmi Manager');
        
        $message = "Welcome to my first Zend Project aka Ezmi";
        $this->view->assign("message", $message);
    }


}

