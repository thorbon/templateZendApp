<?php
/**
 * User model
 *
 * CREATE TABLE `acl_users` (
 *      `uid` INT(11) NOT NULL AUTO_INCREMENT,
 *      `role_id` INT(4) NOT NULL,
 *      `firstname` VARCHAR(50) NOT NULL,
 *      `lastname` VARCHAR(50) NOT NULL,
 *      `user_name` VARCHAR(64) NOT NULL,
 *      `password` VARCHAR(500) NOT NULL,
 *      `password_salt` VARCHAR(500) NOT NULL,
 *      `user_status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
 *      PRIMARY KEY (`uid`),
 *      UNIQUE INDEX `user_name` (`user_name`),
 *      INDEX `FK_acl_users_acl_roles` (`role_id`)
 *   )
 */
class Model_Users
{
    /**
     * Status flags for users in the database
     *
     * @var int
     */
    const STATUS_ACTIVE    = 'ACTIVE';    
    const STATUS_INACTIVE  = 'INACTIVE';
    const STATUS_PENDING   = 'PENDING';
    
    /**
     * Login user
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function login($username, $password)
    {
        if(!strlen($username) || !strlen($password)) {
            return false;
        }
        
        //site wide password salt
        $staticSalt = Zend_Registry::get('config')->auth->salt;

        /**
        *  Setup Auth Adapter
        *  Note: Zend_Registry::get('db') represents the database adapter you have instantiated elsewhere
        */
        
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('Zend_Web_Db'));
        $authAdapter->setTableName('acl_users')
                    ->setIdentityColumn('user_name')
                    ->setCredentialColumn('password')
                    ->setIdentity($username)
                    ->setCredential($password);
        
        /**
         * The password is a SHA1 hash of the site salt, the password, and
         * the user's salt (in the database). Also only active users can login
         */
        $authAdapter->setCredentialTreatment(
            "SHA1(CONCAT('$staticSalt',?,password_salt))"
            . " AND user_status= '" . self::STATUS_ACTIVE . "'"
        );

        //Authenticate
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);
        if (!$result->isValid()) {
            //echo "login failed<br>";
            return false;
        }
        
        //get the matching row and persist to session
        $row = $authAdapter->getResultRowObject(array(
            'uid',
            'role_id',
            'user_name',
            'lastname',
            'firstname',
        ));
        $auth->getStorage()->write($row);

        //login successful
        self::getUserMenu();
        return true;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        //clear auth and user sessions
        if(Zend_Auth::getInstance()->hasIdentity()){
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::destroy(true, true);
        }
    }
    

    /**
     * Get the user detail of logged in user
     *
     * @param string $name
     * @return false|string
     */
    public static function getLoggedInUserField($name)
    {
        if(!$name) {
            return false;
        }
         
        //load user auth details
        $user = Zend_Auth::getInstance()->getIdentity();

        //if field is defined in auth identity
        if($user && isset($user->$name)) {
            return $user->$name;
        }

        return false;
    }
    
    
    /**
     *
     * Check if the user is an administrator
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return 1 == self::getLoggedInUserField('role_id');
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }
    
    public static function getUserMenu()
    {
        $username = self::getLoggedInUserField('user_name');
        
        $acl = new My_Acl($username);
        
        $menu = array();
        
        foreach ($acl->listResources() as $key=>$r) {
 
            try 
            { 
                if ($acl->isUserAllowed($r['resource'], $r['permission_name'])) {
                    if($r['display_in_menu'] == 'Y') {
                        $menu[$r['resource_menu_name']]['<li><a href="/templateZendApp/web/'. $r['resource'] . '/' . $r['permission_name'] .'">'.$r['permission_menu_name'].'</a><li>'] = null;
                    }
                }
            } 
            catch (Zend_Acl_Exception $e) 
            {
                print_r ($e->getMessage());
            }
        }
        
        $session = Zend_Registry::get('Zend_Session');
        $session->user['menu'] = $menu;
        return $menu;
    }

}