<?php
/**
 * Centralised user auth
 *
 * @author Ricardo Hamilton
 *
 */
class My_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * Predispatch method to authenticate user
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {        
        if ('web' == $request->getModuleName()) 
        {
            $session = Zend_Registry::get('Zend_Session');
            $logger = Zend_Registry::get('Zend_Log');
            
            if (time() > ($session->startId + $session->timeout)) {
                $session->expired = true;
            }
            
            $logger->info(__CLASS__.' > '.__FUNCTION__.' initialized - '.time());
            
            $logger->info("User not logged in");
            
            /**
             * User not logged in.
             * 
             * Any requests coming from the AuthController will continue normally
             */
            if ('auth' == $request->getControllerName()) {
                return;
            }
            
            if (Model_Users::isLoggedIn()) {
                $logger->info("Session reset");
                $session->setExpirationSeconds($session->timeout);
                $layout = Zend_Layout::getMvcInstance();
                $view = $layout->getView();
        
                $firstname = Model_Users::getLoggedInUserField('firstname');
                $lastname = Model_Users::getLoggedInUserField('lastname');
                
                $acl = new My_Acl(Model_Users::getLoggedInUserField('user_name'));
                
                if (!$acl->isUserAllowed($request->getControllerName(), $request->getActionName())) {
                    /*$request->setModuleName('web')
                            ->setControllerName('dashboard')
                            ->setActionName('main')
                            ->setDispatched(FALSE);*/
                    $view->action('main', 'dashboard', 'web');
                }
                
                $role = Zend_Registry::get('User_Role');
                
                $view->headerInfo = "User $firstname $lastname ($role role) is logged in (<a href='/templateZendApp/web/auth/logout'>logout</a>)";
                                
                /**
                * @internal
                * Used to build the User Menu 
                */
                /*foreach ($session->user['menu'] as $key => $value) {
                    
                    $subMenuList = '<ul>';
                    foreach ($value as $k=>$v) {
                        $subMenuList .= $k;
                    }
                    $subMenuList .= '</ul>';
                    
                    $view->usermenu .= $view->accordionPane(strtolower($key),
                                               $subMenuList,
                                               array('region' => 'top','title'=>$key),
                                               array('style' => 'background-color: white;')
                                            );
                }*/
                
                $subMenuList = '<div class="clean demo-container"><ul class="accordion"  id="accordion-3">';
                foreach ($session->user['menu'] as $key => $value) {
                                
                    $subMenuList .= '<li><a href="#">'.$key.'</a><ul>';
                    foreach ($value as $k=>$v) {
                        $subMenuList .= $k;
                    }
                    $subMenuList .= '</ul></li>';
                }
                $subMenuList .= '</ul></div>';
                
                $view->usermenu = $subMenuList;
                
                //$logger->info("Menu: {$view->usermenu}");
                
                return;
            }
            
            $request->setModuleName('web')
                    ->setControllerName('auth')
                    ->setActionName('login')
                    ->setDispatched(TRUE);
        }
    }
} 