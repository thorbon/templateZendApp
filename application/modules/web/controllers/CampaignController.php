<?php

class Web_CampaignController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $this->view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        
        // setting the controller and action name as title segments:
        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->view->headTitle($this->request->getControllerName())
             ->headTitle($this->request->getActionName());
             
        $user = new Model_Users();
        $username = $user->getLoggedInUserField('user_name');
        
        $this->_acl = new My_Acl($username);
        
        $this->_db = Zend_Registry::get('Zend_Web_Db');
        $this->logger = Zend_Registry::get('Zend_Log');
    }
    
    public function statisticsAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
    
    public function editlistAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
    
    public function viewlistAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
    
    public function menuAction()
    {
        $userMenu = new My_Forms_UserMenu();
        $this->view->umenu = $userMenu;
    }
}
