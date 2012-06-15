<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id: Select.php 1605M 2011-01-31 03:57:39Z (local) $
 * @link      http://zfdatagrid.com
 */

class Bvb_Grid_Source_Zend_Select
    extends Bvb_Grid_Source_Db_DbAbstract
        implements Bvb_Grid_Source_SourceInterface
{

    /**
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    /**
     *
     * @var string
     */
    protected $_server;
    /**
     *
     * @var array
     */
    protected $_describeTables;
    /**
     *
     * @var Zend_Cache
     */
    protected $_cache;
    /**
     *
     * @var array
     */
    protected $_fields;
    /**
     *
     * @var mixed
     */
    protected $_totalRecords = null;

    /**
     * Default limit offset
     *
     * @var mixed
     */
    protected $_limit = null;

    /**
     * Class construct.
     *
     * @param Zend_Db_Select $select Select instance
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function __construct(Zend_Db_Select $select)
    {
        if (count($select->getPart('UNION')) > 0) {
            throw new Bvb_Grid_Exception('UNION queries not supported yet');
        }

        $this->_select = $select;
        $this->_limit = $this->_select->getPart(Zend_Db_Select::LIMIT_COUNT);
        $this->init($this->_select);
        return $this;
    }

    /**
     * Define the query using Zend_Db_Select instance
     *
     * @param Zend_Db_Select $select Zend_Db_Select instance
     *
     * @return $this
     */
    public function init(Zend_Db_Select $select)
    {
        $this->_setDb($select->getAdapter());
        $adapter = get_class($select->getAdapter());
        $adapter = str_replace("Zend_Db_Adapter_", "", $adapter);

        if (stripos($adapter, 'mysql') !== false) {
            $this->_server = 'mysql';
        } else {
            $adapter = str_replace('Pdo_', '', $adapter);
            $this->_server = strtolower($adapter);
        }

        return $this;
    }

    /**
     * Set db
     *
     * @param Zend_Db_Adapter_Abstract $db DB instance
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    protected function _setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }

    /**
     * Checks id this data source supports CRUD operations
     *
     * @return true
     */
    public function hasCrud()
    {
        return true;
    }

    /**
     * Returns a specified record
     *
     * @param string $table     Table Name
     * @param array  $condition Conditions to build query
     *
     * @return false|array
     */
    public function getRecord($table, array $condition)
    {
        $select = new Zend_Db_Select($this->_getDb());
        $select->from($table);

        foreach ($condition as $field => $value) {
            if (stripos($field, '.') !== false) {
                $field = substr($field, stripos($field, '.') + 1);
            }

            $select->where($field . '=?', $value);
        }

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $final = $select->query(Zend_Db::FETCH_ASSOC);
                $return = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $final = $select->query(Zend_Db::FETCH_ASSOC);
            $return = $final->fetchAll();
        }

        $final = array();

        foreach ($return[0] as $key => $value) {
            $final[$key] = $value;
        }

        if (count($final) == 0) {
            return false;
        }

        return $final;
    }

    /**
     * Build the fields based on Zend_Db_Select
     *
     * @return array
     */
    public function buildFields()
    {
        $fields = $this->_select->getPart(Zend_Db_Select::COLUMNS);
        $tables = $this->_select->getPart(Zend_Db_Select::FROM);

        $returnFields = array();

        foreach ($fields as $field => $value) {
            /**
             * Select all fields from the table
             */
            if ((string) $value[1] == '*') {
                if (array_key_exists($value[0], $tables)) {
                    $tableFields = $this->getDescribeTable($tables[$value[0]]['tableName']);
                }
                $tableFields = array_keys($tableFields);

                foreach ($tableFields as $field) {
                    $title = ucwords(str_replace('_', ' ', $field));
                    $returnFields[$field] = array('title' => $title, 'field' => $value[0] . '.' . $field);
                }
            } else {
                if (is_object($value[1])) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[2]);
                } elseif (strlen($value[2]) > 0) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                } else {
                    $title = ucwords(str_replace('_', ' ', $value[1]));
                    $returnFields[$value[1]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                }
            }
        }

        $this->_fields = $returnFields;

        return $returnFields;
    }

    /**
     * Get table description and then save it to a array.
     *
     * @param array|string $table Table Name
     * @return array
     */
    public function getDescribeTable($table)
    {
        if (!isset($this->_describeTables[$table]) || !is_array($this->_describeTables[$table])) {
            if ($this->_cache['use'] == 1) {
                $hash = 'Bvb_Grid' . md5($table);
                if (!$result = $this->_cache['instance']->load($hash)) {
                    $result = $this->_getDb()->describeTable($table);
                    $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
                }
            } else {
                $result = $this->_getDb()->describeTable($table);
            }
            $this->_describeTables[$table] = $result;
        }

        return $this->_describeTables[$table];
    }

    /**
     * Returns current db information
     *
     * @return Zend_Db_Select
     */
    protected function _getDb()
    {
        return $this->_db;
    }


    protected function _prepareExecute()
    {
        if ($this->_server == 'mysql') {
            $ghostColumn = $this->getColumns();

            $this->_select->reset('columns');

            $this->_select->columns(array('ZFG_GHOST' => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS 1+1")));

            foreach ($ghostColumn as $value) {
                if ($value[2] == 'ZFG_GHOST') continue;

                if (is_object($value[1])) {
                    $this->_select->columns(array($value[2] => $value[1]), $value[0]);
                } elseif ($value[2] != '') {
                    $this->_select->columns(array($value[2] => $value[1]), $value[0]);
                } else {
                    $this->_select->columns($value[1], $value[0]);
                }
            }
        }
    }

    /**
     * Executes the current query and returns an associative array of results
     *
     * @return array
     */
    public function execute()
    {

        $this->_prepareExecute();

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($this->_select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchAll();

            if ($this->_server == 'mysql') {
                $this->_totalRecords = $this->_select->getAdapter()->fetchOne('select FOUND_ROWS()');
            }
        }

        return $result;
    }

    /**
     * Get full details for a given record
     *
     * @param array $where Conditions to build query
     *
     * @return false|array
     */
    public function fetchDetail(array $where)
    {
        foreach ($where as $field => $value) {
            if (array_key_exists($field, $this->_fields)) {
                $field = $this->_fields[$field]['field'];
            }
            $this->_select->where($field . '=?', $value);
        }

        $this->_select->reset(Zend_Db_Select::LIMIT_COUNT);
        $this->_select->reset(Zend_Db_Select::LIMIT_OFFSET);

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($this->_select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchAll();
        }

        if (!isset($result[0])) {
            return false;
        }

        return $result[0];
    }

    /**
     * Count the rows total without the limit
     *
     * @return int
     */
    public function getTotalRecords()
    {
        if (!is_null($this->_totalRecords)) {

            if ($this->_totalRecords > $this->_limit && $this->_limit > 0) {
                return $this->_limit;
            }
            return $this->_totalRecords;
        }

        $hasExp = false;

        $selectCount = clone $this->_select;

        foreach ($selectCount->getPart('columns') as $value) {
            if ($value[1] instanceof Zend_Db_Expr) {
                $hasExp = true;
                break;
            }
        }

        if ($hasExp == false) {
            $selectCount->reset(Zend_Db_Select::COLUMNS);
            $selectCount->columns(new Zend_Db_Expr('COUNT(*) AS TOTAL'));
        }

        $selectCount->reset(Zend_Db_Select::LIMIT_OFFSET);
        $selectCount->reset(Zend_Db_Select::LIMIT_COUNT);
        $selectCount->reset(Zend_Db_Select::ORDER);

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($selectCount->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $final = $selectCount->query(Zend_Db::FETCH_ASSOC);
                $result = array_change_key_case($final->fetch(), CASE_UPPER);
                $count = (int) $result['TOTAL'];
                $this->_cache['instance']->save($count, $hash, array($this->_cache['tag']));
            }
        } else {
            $final = $selectCount->query(Zend_Db::FETCH_ASSOC);
            $result = array_change_key_case($final->fetch(), CASE_UPPER);
            $count = (int) $result['TOTAL'];
        }


        if ($count > $this->_limit && $this->_limit > 0) {
            return $this->_limit;
        }

        return $count;
    }

    /**
     * This method will fetch fields and return there values.
     * Those values will be used to build mass actions id's
     *
     * @param string $table  Table Name
     * @param string $fields Fields to fetch
     *
     * @return string
     */
    public function getMassActionsIds($table, $fields)
    {
        $select = clone $this->_select;

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::ORDER);

        if (count($fields) == 0) {
            $pks = $this->getIdentifierColumns($table);
        } else {
            $pks = $fields;
        }

        if (count($pks) > 1) {
            $concat = '';
            foreach ($pks as $conc) {
                $concat .= $this->_getDb()->quoteIdentifier($conc) . " ,'-' ,";
            }
            $concat = rtrim($concat, "'-' ,");
        } else {
            $concat = $pks[0];
        }

        $select->columns(array('ids' => new Zend_Db_Expr("CONCAT($concat)")));

        $final = $select->query(Zend_Db::FETCH_ASSOC);
        $result = $final->fetchAll();

        $return = array();
        foreach ($result as $value) {
            $return[] = $value['ids'];
        }

        return implode(',', $return);
    }

    /**
     * Returns the current table list
     *
     * @return Zend_Db_Select
     */
    public function getTableList()
    {
        return $this->_select->getPart(Zend_Db_Select::FROM);
    }

    /**
     * This method will check field type and, if possible,
     * build an array of possible values for filtering
     *
     * @param string $field Field Name
     *
     * @return array
     */
    public function getFilterValuesBasedOnFieldDefinition($field)
    {
        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if (array_key_exists($tableName, $tableList)) {
            $schema = $tableList[$tableName]['schema'];
            $tableName = $tableList[$tableName]['tableName'];
        }

        $table = $this->getDescribeTable($tableName, $schema);

        if (!isset($table[$field])) {
            return 'text';
        }
        $type = $table[$field]['DATA_TYPE'];

        $return = 'text';

        if (substr($type, 0, 4) == 'enum') {
            preg_match_all('/\'(.*?)\'/', $type, $result);

            $return = array_combine($result[1], $result[1]);
        }

        return $return;
    }

    /**
     * Returns field type
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function getFieldType($field)
    {
        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if (array_key_exists($tableName, $tableList)) {

            if ($tableList[$tableName]['schema'] != null) {
                $tableName = $tableList[$tableName]['schema'] . "." . $tableList[$tableName]['tableName'];
            } else {
                $tableName = $tableList[$tableName]['tableName'];
            }

            $schema = $tableList[$tableName]['schema'];
        }

        $table = $this->getDescribeTable($tableName, $schema);
        $type = $table[$field]['DATA_TYPE'];

        if (substr($type, 0, 3) == 'set') {
            return 'set';
        }

        return $type;
    }

    /**
     * Returns a list of current tables used in queries
     *
     * @return array
     */
    public function getMainTable()
    {
        $return = array();

        $from = $this->_select->getPart(Zend_Db_Select::FROM);

        foreach ($from as $key => $tables) {

            if ($tables['joinType'] == 'from' || count($from) == 1) {
                $return['table'] = $tables['tableName'];
                $return['schema'] = $tables['schema'];
                break;
            }
        }

        if (count($return) == 0) {
            $table = reset($from);
            $return['table'] = $table['tableName'];
        }

        return $return;
    }

    /**
     * Builds query order
     *
     * @param string $field Field name
     * @param string $order Query Sort Order
     * @param bool   $reset If we should reset the current order
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function buildQueryOrder($field, $order, $reset = false)
    {
        if (!array_key_exists($field, $this->_fields)) {
            return $this;
        }

        foreach ($this->_select->getPart(Zend_Db_Select::COLUMNS) as $col) {
            if (($col[0] . '.' . $col[2] == $field) && is_object($col[1])) {
                $field = $col[2];
            }
        }

        if ($reset === true) {
            $this->_select->reset('order');
        }

        $this->_select->order($field . ' ' . $order);
        return $this;
    }

    /**
     * Set's the query limit
     *
     * @param int $start  Offset Start
     * @param int $offset Offset End
     *
     * @return Zend_Db_Select
     */
    public function buildQueryLimit($start, $offset)
    {
        $this->_select->limit($start, $offset);
        return $this;
    }

    /**
     * Returns Zend_Db_Select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelectObject()
    {
        return $this->_select;
    }

    /**
     * Returns current query order
     *
     * @return mixed
     */
    public function getSelectOrder()
    {
        $result = $this->_select->getPart(Zend_Db_Select::ORDER);

        if (count($result) == 0) {
            return array();
        }

        return $result[0];
    }

    /**
     * This method will return an associative array
     * for use in filters
     *
     * @param string $field      Field Name
     * @param string $fieldValue Field Value
     * @param string $order      Query Sort Order
     *
     * @return array
     */
    public function getDistinctValuesForFilters($field, $fieldValue, $order = 'name ASC')
    {
        $distinct = clone $this->_select;

        $distinct->reset(Zend_Db_Select::COLUMNS);
        $distinct->reset(Zend_Db_Select::ORDER);
        $distinct->reset(Zend_Db_Select::LIMIT_COUNT);
        $distinct->reset(Zend_Db_Select::LIMIT_OFFSET);

        $distinct->columns(array('field' => new Zend_Db_Expr("DISTINCT({$field})")));
        $distinct->columns(array('value' => $fieldValue));
        $distinct->order($order);

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($distinct->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $result = $distinct->query(Zend_Db::FETCH_ASSOC);
                $result = $result->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $distinct->query(Zend_Db::FETCH_ASSOC);
            $result = $result->fetchAll();
        }

        $final = array();

        foreach ($result as $value) {
            $final[$value['field']] = $value['value'];
        }

        return $final;
    }

    /**
     * This method will return a associative array for building filters
     * based on a table query
     *
     * @param string $table      Table Name
     * @param string $field      Field's Name
     * @param string $fieldValue Field's value
     * @param string $order      Query Order
     *
     * @return array
     */
    public function getValuesForFiltersFromTable($table, $field, $fieldValue, $order = 'name ASC')
    {

        $select = $this->_getDb()
            ->select()
            ->from($table, array('field' => $field, 'value' => $fieldValue))
            ->order($order);

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $result = $select->query(Zend_Db::FETCH_ASSOC);
                $result = $result->fetchAll();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $select->query(Zend_Db::FETCH_ASSOC);
            $result = $result->fetchAll();
        }

        $final = array();

        foreach ($result as $value) {
            $final[$value['field']] = $value['value'];
        }

        return $final;
    }

    /**
     * Returns Sql expressions
     *
     * @param array $value SQL options
     * @param array $where Additional where condition
     *
     * @return int
     */
    public function getSqlExp(array $value, $where = array())
    {
        $cols = array();
        foreach ($this->_select->getPart('columns') as $col) {
            if ($col[1] instanceof Zend_Db_Expr) {
                $cols[$col[2]] = $col[1]->__toString();
            }
        }

        if (array_key_exists($value['value'], $cols)) {
            $value['value'] = $cols[$value['value']];
        }

        $valor = '';
        foreach ($value['functions'] as $final) {
            $valor .= $final . '(';
        }
        $valor .= $value['value'] . str_repeat(')', count($value['functions']));

        $select = clone $this->_select;
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);
        $select->columns(new Zend_Db_Expr($valor . ' AS TOTAL'));

        foreach ($where as $key => $value) {
            if (strlen(trim($value)) < 1) {
                continue;
            }
            $select->where($key . '=?', $value);
        }

        if ($this->_cache['use'] == 1) {
            $hash = 'Bvb_Grid' . md5($select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $final = $select->query(Zend_Db::FETCH_ASSOC);
                $result = $final->fetchColumn();
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $final = $select->query(Zend_Db::FETCH_ASSOC);
            $result = $final->fetchColumn();
        }

        return $result;
    }

    /**
     * Returns current columns in use
     *
     * @return Zend_Db_Select
     */
    public function getColumns()
    {
        return $this->_select->getPart('columns');
    }

    /**
     * Adds a full-text search to the query
     *
     * @param string $filter Filter type to be applyed
     * @param string $field  Field Name
     *
     * @return void
     */
    public function addFullTextSearch($filter, $field)
    {
        $full = $field['search'];

        if (!isset($full['indexes'])) {
            $indexes = $field['field'];
        } elseif (is_array($full['indexes'])) {
            $indexes = implode(',', array_values($full['indexes']));
        } elseif (is_string($full['indexes'])) {
            $indexes = $full['indexes'];
        }

        $extra = isset($full['extra']) ? $full['extra'] : 'boolean';

        if (!in_array($extra, array('boolean', 'queryExpansion', false))) {
            throw new Bvb_Grid_Exception('Unrecognized value in extra key');
        }

        if ($extra == 'boolean') {
            $extra = 'IN BOOLEAN MODE';
        } elseif ($extra == 'queryExpansion') {
            $extra = ' WITH QUERY EXPANSION ';
        } else {
            $extra = '';
        }

        if ($extra == 'IN BOOLEAN MODE') {
            $filter = preg_replace("/\s+/", " +", $this->_getDb()->quote(' ' . $filter));
        } else {
            $filter = $this->_getDb()->quote($filter);
        }

        $this->_select->where(new Zend_Db_Expr("MATCH ($indexes) AGAINST ($filter $extra) "));
        return;
    }

    /**
     * Returns a quoted value
     *
     * @param string $value Value to be quoted
     *
     * @return string
     */
    public function quoteValue($value)
    {
        return $this->_getDb()->quote($value);
    }

    /**
     * Add's a new condition to the curretn query
     *
     * @param string $filter        Filter to apply
     * @param string $op            Condition option
     * @param array  $completeField All fields options
     *
     * @return void
     */
    public function addCondition($filter, $op, $completeField)
    {


        $op = strtolower($op);

        $explode = explode('.', $completeField['field']);
        $field = end($explode);

        $simpleField = false;

        $columns = $this->getColumns();

        foreach ($columns as $value) {
            if ($field == $value[2]) {
                if (is_object($value[1])) {
                    $field = $value[1]->__toString();
                    $simpleField = true;
                } else {
                    $field = $value[0] . '.' . $value[1];
                }
                break;
            } elseif ($field == $value[0]) {
                $field = $value[0] . '.' . $value[1];
                break;
            }
        }

        if (strpos($field, '.') === false && $simpleField === false) {
            $field = $completeField['field'];
        }


        /**
         * Reserved words from myslq dont contain any special charaters.
         * But select expressions may.
         *
         * SELECT IF(City.Population>500000,1,0)....
         *
         * We can not quoteIdentifier this fields...
         *
         */
        if (strpos($field, '(') !== false) {
            $field = $this->_getDb()->quoteIdentifier($field);
        }

        $func = 'where';

        if (strpos($field, '(') !== false) {
            $func = 'having';
        }

        switch ($op) {
            case 'sqlexp':
                $this->_select->$func(new Zend_Db_Expr($filter));
                break;
            case 'empty':
                $this->_select->$func($field . " = '' ");
                break;
            case 'isnull':
                $this->_select->$func(new Zend_Db_Expr($field . ' IS NULL '));
                break;
            case 'isnnotull':
                $this->_select->$func(new Zend_Db_Expr($field . ' IS NOT NULL '));
                break;
            case 'equal':
            case '=':
                $this->_select->$func($field . ' = ?', $filter);
                break;
            case 'rege':
                $this->_select->$func($field . " REGEXP " . $this->_getDb()->quote($filter));
                break;
            case 'rlike':
                $this->_select->$func($field . " LIKE " . $this->_getDb()->quote($filter . "%"));
                break;
            case 'llike':
                $this->_select->$func($field . " LIKE " . $this->_getDb()->quote("%" . $filter));
                break;
            case '>=':
                $this->_select->$func($field . " >= ?", $filter);
                break;
            case '>':
                $this->_select->$func($field . " > ?", $filter);
                break;
            case '<>':
            case '!=':
                $this->_select->$func($field . " <> ?", $filter);
                break;
            case '<=':
                $this->_select->$func($field . " <= ?", $filter);
                break;
            case '<':
                $this->_select->$func($field . " < ?", $filter);
                break;
            case 'in':
                $filter = explode(',', $filter);
                $this->_select->$func($field . " IN  (?)", $filter);
                break;
            case 'flag':
                $this->_select->$func($field . " & ? <> 0", $filter);
                break;
            case '||':
                $this->_select->orWhere($field . " LIKE " . $this->_getDb()->quote("%" . $filter . "%"));
                break;
            case 'range':
            case '&':
            case 'and':
                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $this->_select->$func(
                    $field . " between " . $this->_getDb()
                        ->quote($start) . " and " . $this->_getDb()
                        ->quote($end)
                );
                break;
            case 'like':
            default:
                $this->_select->$func($field . " LIKE " . $this->_getDb()->quote("%" . $filter . "%"));
                break;
        }
    }

    /**
     * Get's current source name
     *
     * @return string
     */
    public function getSourceName()
    {
        return $this->_server;
    }

    /**
     * Inserts a new record in the database
     *
     * @param string $table Table Name
     * @param array  $post  Values to insert
     *
     * @return mixed
     */
    public function insert($table, array $post)
    {
        if ($this->_cache['use'] == 1) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        $this->_getDb()->insert($table, $post);
        return $this->_getDb()->lastInsertId();
    }

    /**
     * Updates a given record
     *
     * @param string $table     Table Name
     * @param array  $post      Values to be inserted in the database
     * @param array  $condition Condition to add to the query for the update method
     *
     * @return mixed
     */
    public function update($table, array $post, array $condition)
    {
        if ($this->_cache['use'] == 1) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->_getDb()->update($table, $post, $this->buildWhereCondition($condition));
    }

    /**
     * Deletes a record from the database
     *
     * @param string $table     Table Name
     * @param array  $condition Conditions to add
     *
     * @return mixed
     */
    public function delete($table, array $condition)
    {
        if ($this->_cache['use'] == 1) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->_getDb()->delete($table, $this->buildWhereCondition($condition));
    }

    /**
     * Buils query where condition
     *
     * @param array $condition Conditions to add to the query
     *
     * @return string
     */
    public function buildWhereCondition(array $condition)
    {
        $where = '';
        foreach ($condition as $field => $value) {

            if (stripos($field, '.') !== false) {
                $field = substr($field, stripos($field, '.') + 1);
            }

            $where .= 'AND ' . $this->_getDb()->quoteIdentifier($field) . ' = ' . $this->_getDb()->quote($value) . ' ';
        }
        return " (" . substr($where, 3) . ")";
    }

    /**
     * Resets current query order
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function resetOrder()
    {
        $this->_select->reset('order');
        return $this;
    }

    /**
     * Resets query limit
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function resetLimit()
    {
        $this->_select->reset('limitcount');
        $this->_select->reset('limitoffset');
        return $this;
    }

    /**
     * Defines cache options
     *
     * @param array $cache Cache options
     *
     * @return array
     */
    public function setCache($cache)
    {
        if (!is_array($cache)) {
            $cache = array('use' => 0);
        }

        if (isset($cache['use']['db']) && $cache['use']['db'] == 1) {
            $cache['use'] = 1;
        } else {
            $cache['use'] = 0;
        }

        $this->_cache = $cache;
    }

    /**
     * Builds form
     *
     * @param array $inputsType Inputs type
     *
     * @return array
     */
    public function buildForm($inputsType = array())
    {
        $table = $this->getMainTable();
        $cols = $this->getDescribeTable($table['table'], $table['schema']);

        return $this->buildFormElements($cols, array(), $inputsType);
    }

    /**
     * Builds form elements
     *
     * @param type  $cols       Columns to build
     * @param array $info       Optional - Model Info
     * @param type  $inputsType Elements type (password, text, select...)
     *
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function buildFormElements($cols, $info = array(), $inputsType = array())
    {
        $final = array();
        $form = array();

        $return = array();

        foreach ($cols as $column => $detail) {

            $label = ucwords(str_replace('_', ' ', $column));

            $next = false;

            if ($detail['PRIMARY'] == 1) {
                continue;
            }

            if (!isset($info['referenceMap'])) {
                $info['referenceMap'] = array();
            }

            if (count($info['referenceMap']) > 0) {
                foreach ($info['referenceMap'] as $dep) {
                    if (is_array($dep['columns']) && in_array($column, $dep['columns'])) {
                        $refColumn = $dep['refColumns'][array_search($column, $dep['columns'])];
                    } elseif (is_string($dep['columns']) && $column == $dep['columns']) {
                        $refColumn = $dep['refColumns'];
                    } else {
                        continue;
                    }

                    $t = new $dep['refTableClass']();

                    $in = $t->info();

                    if ((count($in['cols']) == 1 && count($in['primary']) == 0) || count($in['primary']) > 1) {
                        throw new Exception('Columns:' . count($in['cols']) . ' Keys:' . count($in['primary']));
                    }

                    if (count($in['primary']) == 1) {
                        $field1 = array_shift($in['primary']);
                        $field2 = $refColumn;
                    }

                    $final['values'][$column] = array();
                    $r = $t->fetchAll()->toArray();

                    if ($detail['NULLABLE'] == 1) {
                        $final['values'][$column][""] = "-- Empty --";
                    }

                    foreach ($r as $field) {
                        $final['values'][$column][$field[$field1]] = $field[$field2];
                    }

                    $return[$column] = array('type' => 'select',
                                             'label' => $label,
                                             'default' => $final['values'][$column]);

                    $next = true;
                }
            }

            if ($next === true) {
                continue;
            }

            if (stripos($detail['DATA_TYPE'], 'enum') !== false) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ($result[1] as $match) {
                    $options[$match] = ucfirst($match);
                }

                $return[$column] = array('type' => 'select',
                                         'label' => $label,
                                         'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                         'default' => $options);

                continue;
            }

            if (stripos($detail['DATA_TYPE'], 'set') !== false) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ($result[1] as $match) {
                    $options[$match] = ucfirst($match);
                }

                $return[$column] = array('type' => 'multiSelect',
                                         'label' => $label,
                                         'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                         'default' => $options);
                continue;
            }

            switch ($detail['DATA_TYPE']) {
                case 'time':
                    $return[$column] = array('type' => 'time',
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;
                case 'date':
                    $return[$column] = array('type' => 'date',
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;
                case 'datetime':
                case 'timestamp':
                    $return[$column] = array('type' => 'datetime',
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $return[$column] = array('type' => 'longtext',
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $zero = (!is_null($detail['DEFAULT']) && $detail['DEFAULT'] == "0") ? true : false;
                    $return[$column] = array('type' => 'number',
                                             'label' => $label,
                                             'required' => ($zero == false && $detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $return[$column] = array('type' => 'decimal',
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;

                default:
                case 'varchar':
                case 'char':
                    $length = $detail['LENGTH'];
                    $return[$column] = array('type' => 'smallText',
                                             'length' => $length,
                                             'label' => $label,
                                             'required' => ($detail['NULLABLE'] == 1) ? false : true,
                                             'default' => (!is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : ""));
                    break;
            }
        }

        $form = $this->buildFormElementsFromArray($return);

        foreach ($inputsType as $field => $type) {
            $form['elements'][$field][0] = strtolower($type);
        }

        return $form;
    }

    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @var string $table Source table to fecth column identifiers
     *
     * @return array
     */
    public function getIdentifierColumns($table)
    {
        $pk = $this->getDescribeTable($table);
        $tb = $this->getTableList();

        $keys = array();

        $hasSerial = false;

        if (is_array($pk)) {
            foreach ($pk as $pkk => $primary) {
                if ($primary['IDENTITY'] == 1) {
                    $hasSerial = true;

                    foreach ($tb as $key => $value) {
                        if ($value['tableName'] == $primary['TABLE_NAME']) {
                            $prefix = $key . '.';
                            break;
                        }
                    }
                    $keys[] = $prefix . $pkk;
                }
            }

            if ($hasSerial === false) {
                foreach ($pk as $pkk => $primary) {
                    if ($primary['PRIMARY'] == 1) {
                        foreach ($tb as $key => $value) {
                            if ($value['tableName'] == $primary['TABLE_NAME']) {
                                $prefix = $key . '.';
                                break;
                            }
                        }
                        $keys[] = $prefix . $pkk;
                    }
                }
            }
        }

        return $keys;
    }

}