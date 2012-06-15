<?php

class Web_DashboardController extends Zend_Controller_Action
{
    private $session;
    
    public function init()
    {
        // initialize code
        $this->session = Zend_Registry::get('Zend_Session');
    }
    
    public function mainAction()
    {
        //main code
        $this->view->userMenu = $this->session->user['menu'];
    }
    
    public function preferencesAction()
    {
        // code here
    }
}