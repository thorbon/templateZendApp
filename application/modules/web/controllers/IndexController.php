<?php

class Web_IndexController extends Zend_Controller_Action
{
    private $_acl;
    
    public function init()
    {
        /* Initialize action controller here */
        $this->view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        //$this->view->headScript()->appendFile($this->view->baseUrl().'/public/js/jquery-ui-timepicker-addon.js');
            
        // setting the site in the title; possibly in the layout script:
        //$this->view->headTitle('Ezmi - Web Manager');
        
        // setting the controller and action name as title segments:
        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->view->headTitle($this->request->getControllerName())
             ->headTitle($this->request->getActionName());
        
        $this->_acl = new My_Acl($this->_getParam('user'));
    }

    public function indexAction()
    {
        $data = new Model_Data();
        $incomingMessages = $data->fetchAllIncomingSms();
        //$result = $data->findClient('e');
        
        //$this->view->assign('pageTitle', 'Web Manager');
        $this->view->assign('incomingMessages', $incomingMessages);
        
        //$this->view->autoCompleteElement = new ZendX_JQuery_Form_Element_AutoComplete('ac');
        //$this->view->autoCompleteElement->setJQueryParam('source', array('Test', 'Essen', 'Mario', 'Metroid'));
    }
    
    public function findclientAction()
    {
        $search = $this->_getParam('term');
        
        $data = new Model_Data();
        $result = $data->findClient($search);
        $this->_helper->json(array_values($result));
    }
    
    public function preferencesAction()
    {
        $this->view->availablePrefs = $this->_acl->listResourcesByGroup('manage');
 
        $this->view->user = $this->_acl->_user;
        $this->view->role = $this->_acl->_getUserRoleName;
        
        $menu = array();
        $i = 0;
 
        foreach ($this->_acl->listResources() as $key=>$r) {
 
            try 
            {
                //$s[$r['resource'].' - '.$r['permission_name']] = $this->_acl->isUserAllowed($r['resource'], $r['permission_name']) ? '<font color="green">allowed</font>' : '<font color="red">denied</font>';
                
                if ($this->_acl->isUserAllowed($r['resource'], $r['permission_name']))
                {
                    $s[$r['resource'].' - '.$r['permission_name']] = '<font color="green">allowed</font>';
                    
                    $menu[$r['resource_menu_name']]['<a href="#">'.$r['permission_menu_name'].'</a>'] = null;
                    
                    //$menu[] = array($r['resource_menu_name']);
                }
                else
                {
                    $s[$r['resource'].' - '.$r['permission_name']] = '<font color="red">denied</font>';
                }
                
                $this->view->allowed = $s;
                $this->view->menu = $menu;
            } 
            catch (Zend_Acl_Exception $e) 
            {
                print_r ($e->getMessage());
            }
 
        }
    }
    
    public function manageconstituencyAction()
    {
        if ($this->_acl->isUserAllowed($this->request->getControllerName(), $this->request->getActionName())) {
            echo "User <font color='green'><b>{$this->_acl->_user}</b></font> is allowed to access this section.";
        } else {
            echo "User <font color='red'><b>{$this->_acl->_user}</b></font> is NOT allowed to access this section.";
        }
        
        die();
    }
}

