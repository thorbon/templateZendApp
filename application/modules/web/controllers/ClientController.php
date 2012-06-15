<?php

class Web_ClientController extends Zend_Controller_Action
{
    private $params;
    private $user;
    private $get;
    private $_acl;
    private $username;
    private $userId;
    private $logger;
    
    public function init()
    {
        /* Initialize action controller here */
        $this->view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        
        // setting the controller and action name as title segments:
        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->view->headTitle($this->request->getControllerName())
             ->headTitle($this->request->getActionName());
        
        $this->logger = Zend_Registry::get('Zend_Log');
        $this->params = $this->_getAllParams();
        $this->user = new Model_Users();
        
        $this->username = $this->user->getLoggedInUserField('user_name');
        $this->userId = $this->user->getLoggedInUserField('uid');
        
        $this->_acl = new My_Acl($this->username);
    }
    
    public function clientlistAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            $adClientList = array(
                                    array("client_name"=>"Client 1", "country_name"=>"France", "message"=>"This is a new message 1"),
                                    array("client_name"=>"Client 2", "country_name"=>"Ukraine", "message"=>"This is a new message 2"),
                                    array("client_name"=>"Client 3", "country_name"=>"Germany", "message"=>"This is a new message 3"),
                                    array("client_name"=>"Client 4", "country_name"=>"Sweden", "message"=>"This is a new message 4")
                                );
            $this->view->adClientList = $adClientList;
        } else {
            $this->view->errorMessage = "You are not authorized to access this page.";
        }
    }
    
    public function detailsbyclientAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
    
    public function detailsbymessageAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
    
    public function addworkerAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
            
            $this->view->form = new My_Forms_AddWorker();
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
    }
}