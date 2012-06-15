<?php
  
class Model_User
{
    function __construct()
    {
        $this->db = Zend_Registry::get('Zend_Module1_Db');
        $this->logger = Zend_Registry::get('Zend_Log');
    }
    
    /**
     * Retrieves user information
     *
     * @parameter int $arg1 
     * @parameter float $arg2
     * @return array
     */
    function getUser($arg1, $arg2)
    {
        try
        {
            $sql = $this->db->select()
                            ->from(array('T1' => 'table1'))
                            ->where('T1.column1 = ?', $arg1)
                            ->where('T1.column2 = ?', $arg2)
                            ->where('T1.column3 = ?', 'ACTIVE')
                            ->limit(1);
                            
            $data = $this->db->fetchRow($sql);
            
            if ($data)
                return $data;
        }
        catch (Exception $e)
        {
            $this->logger->err(__CLASS__."->".__FUNCTION__.": {$e->getMessage()}");
        }
        
        return false;
    }
}