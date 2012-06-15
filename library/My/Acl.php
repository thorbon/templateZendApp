<?php

class My_Acl extends Zend_Acl {
 
    private $_db;
 
    public $_getUserRoleName = null;
 
    public $_getUserRoleId = null;
 
    public $_user = null;
 
    public function __construct($user)
    {
        $this->_db = Zend_Registry::get('Zend_Web_Db');
        
        $this->_user = $user ? $user : 'Guest';
          
        self::roleResource();
 
        $getUserRole = $this->_db->fetchRow(
        $this->_db->select()
            ->from('acl_roles')
            ->from('acl_users')
            ->where('acl_users.user_name = "' . $this->_user . '"')
            ->where('acl_users.role_id = acl_roles.role_id'));
 
        $this->_getUserRoleId = $getUserRole['role_id'] ? $getUserRole['role_id'] : 5;
        $this->_getUserRoleName = $getUserRole['role_name'] ? $getUserRole['role_name'] : 'Everyone';
 
        $this->addRole(new Zend_Acl_Role($this->_user), $this->_getUserRoleName);
        
        Zend_Registry::set('User_Role', $this->_getUserRoleName);
 
    }
 
    private function initRoles()
    {
        $roles = $this->_db->fetchAll(
        $this->_db->select()
            ->from('acl_roles')
            ->order(array('role_id DESC')));
 
        $this->addRole(new Zend_Acl_Role($roles[0]['role_name']));
 
        for ($i = 1; $i < count($roles); $i++) {
            $this->addRole(new Zend_Acl_Role($roles[$i]['role_name']), $roles[$i-1]['role_name']);
        }
    }
 
    private function initResources()
    {
        self::initRoles();
 
        $resources = $this->_db->fetchAll(
        $this->_db->select()
            ->from('acl_resources'));
 
        foreach ($resources as $key=>$value){
            if (!$this->has($value['resource'])) {
                $this->add(new Zend_Acl_Resource($value['resource']));
            }
        }
    }
 
    private function roleResource()
    {
        self::initResources();
 
        $acl = $this->_db->fetchAll(
        $this->_db->select()
            ->from('acl_roles')
            ->from('acl_resources')
            ->from('acl_permissions')
            ->from('acl_permission')
            ->where('acl_roles.role_id = acl_permissions.role_id')
            ->where('acl_permissions.permission_id = acl_permission.permission_id')
            ->where('acl_resources.uid = acl_permissions.resource_uid'));
 
        foreach ($acl as $key=>$value) {
            $this->{$value['action']}($value['role_name'], $value['resource'],$value['permission_name']);
        }
    }
 
    public function listRoles()
    {
        return $this->_db->fetchAll(
        $this->_db->select()
            ->from('acl_roles'));
    }
 
    public function getRoleId($roleName)
    {
        return $this->_db->fetchRow(
        $this->_db->select()
            ->from('acl_roles', 'role_id')
            ->where('acl_roles.role_name = "' . $roleName . '"'));
    }
 
    public function insertAclUser()
    {
        $data = array(
            'role_id' => $this->_getUserRoleId,
            'user_name' => $this->_user);
 
        return $this->_db->insert('acl_users',$data);
    }
 
    public function listResources()
    {
        return $this->_db->fetchAll(
        $this->_db->select()
            ->from('acl_resources')
            ->from('acl_permissions')
            ->from('acl_permission')
            ->where('resource_uid = uid')
            ->where('acl_permissions.permission_id = acl_permission.permission_id')
            ->order(array('acl_resources.resource_menu_order ASC', 'acl_permission.menu_order ASC', 'acl_permission.permission_menu_name ASC')));
    }
 
    public function listResourcesByGroup($group)
    {
        $result = null;
        $group = $this->_db->fetchAll($this->_db->select()
            ->from('acl_resources')
            ->from('acl_permissions')
            ->from('acl_permission')
            ->where('acl_resources.resource = "' . $group . '"')
            ->where('uid = resource_uid')
            ->where('acl_permissions.permission_id = acl_permission.permission_id')
        );
 
        foreach ($group as $key=>$value) {
            if($this->isAllowed($this->_user, $value['resource'], $value['permission_name'])) {
                $result[] = $value['permission_name'] . ' - ' . $value['action'];
            }
        }
 
        return $result;
    }
 
    public function isUserAllowed($resource, $permission)
    {
        return ($this->isAllowed($this->_user, $resource, $permission));
    }
}