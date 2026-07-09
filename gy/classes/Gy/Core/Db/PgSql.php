<?php

namespace Gy\Core\Db;

use Gy\Core\Storage\Driver;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/** 
 * PgSql - класс для работы с базой данных PostgreSQL
 * class work PostgreSQL
 */
class PgSql extends Driver
{

    public $test = 'pgsql ok';
    public $defaultPort = '5432';
    public $db;

    /* connect() - create connect in database
    * @param $host
    * @param $user
    * @param $pass 
    * @param $nameDb
    * @param $port
    * @return resurs, false
    */
    public function connect($host, $user, $pass, $nameDb, $port)
    {        
        $this->db = pg_connect("host=".$host." port=".$port." dbname=".$nameDb." user=".$user." password=".$pass);
        return $this->db;
    }

    /* query()  - out query in database
     * @param $db - resurs (create self::connect()), $query - string query
     * @return true - ok OR false - not ok
     */
    public function query($query)
    {
        return pg_query($this->db, $query);
    }

    /*  close() - close connect database
     * @param $db - resurs (create self::connect())
     * @return true - ok OR false - not ok
     */
    public function close()
    {
        return pg_close($this->db);
    }

    /**
     * fetch - получить порцию (строку) данных, после выполнения запроса в БД
     * @param $res - результат отработки запроса в БД
     * @return array
     */
    public function fetch($res)
    {
        $result = array();
        if ($res !== false) {
            $result = pg_fetch_assoc($res);
        }
        return $result;
    }

    /**
     * fetchAll - тоже что и fetch только в получит всё в виде массива (с ключём id элемента)
     * @param $res - результат отработки запроса в БД
     * @return array
     */
    public function fetchAll($res, $key = 'id')
    {
        $result = array();
        while ($arRes = self::fetch($res)) {
            if ($key !== false) {
                $result[$arRes[$key]] = $arRes;
            } else {
                $result[] = $arRes;
            }
        }
        return $result;
    }

    public function __construct($dbConfig) 
    {
        if ( empty($this->db)) {
            if (!empty($dbConfig)) {
                if (empty($dbConfig['db_port'])) {
                    $dbConfig['db_port'] = $this->defaultPort;
                }
                $this->connect(
                    $dbConfig['db_host'], 
                    $dbConfig['db_user'], 
                    $dbConfig['db_pass'], 
                    $dbConfig['db_name'], 
                    $dbConfig['db_port']
                );
            }
        }
    }

    /**
     * isOneVersionWhere 
     *  (ru) - проверит соответствует ли условие, условию как ниже (первый вариант)
     *         (пока поддерживается только сравнение и не рано '=', '!=' )
     *  
     *  (en) - will check whether it matches the condition, the condition as below (first option)
     *         (so far only comparison is supported and not early '=', '! =')
     * 
     *  $where = array(
     *    '=' => array(
     *       'login',
     *       'asd2'
     *    )
     *  ) 
     * 
     * @param array $where - 
     *        (ru) - условие (пример выше, что то типа дерева)
     *        (en) - condition (example above, something like a tree)
     * @return boolean
     */        
    private function isOneVersionWhere($where)
    {
        $result = false;
        if (count($where) == 1) {
            foreach ($where as $key => $value) {
                if (in_array($key, array('=', '!=' )) && (count($value) == 2)) {
                    $result = true;
                }
            }
            $value = array_shift($where);

        }
        return $result;
    }

    /**
     * isTwoVersionWhere 
     *  (ru) - проверит соответствует ли условие, условию как ниже (второй вариант)
     *         (пока поддерживается только сравнение и не рано '=', '!=' и связки 'AND', 'OR' )
     *  
     *  (ru) - will check whether the condition matches the condition as below (second option)
     *         (so far only comparison is supported and not early '=', '! =' and the 'AND', 'OR' connectives)
     * 
     *  $where = array(
     *      'OR' => array(
     *          array(
     *              '=' => array(
     *                  'login',
     *                  'asd2'
     *              ),
     *          ),
     *          array(
     *              '!=' => array(
     *                  'login',
     *                  'asd'
     *              ),
     *          ),
     *          array(
     *              '!=' => array(
     *                  'login',
     *                  'asd'
     *              ),
     *          ),
     *      )    
     *  ) 
     * 
     * @param array $where - 
     *        (ru) - условие (пример выше, что то типа дерева)
     *        (en) - condition (example above, something like a tree)
     * @return boolean
     */ 
    private function isTwoVersionWhere($where)
    {
        $result = true;
        foreach ($where as $key => $value) {
            if (in_array($key, array('OR', 'AND'))) {
                foreach ($value as $value2) {
                    if (!$this->isOneVersionWhere($value2)) {
                        $result = false;
                    }
                }
            } else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * getStrOneTypeWhere
     *  (ru) - соберёт строчку с условием определённого вида,
     *         для условий из массива $where (метода например select) 1 варианта
     * 
     *  (en) - will collect a line with a condition of a certain kind,
     *         for conditions from the array $where (for example, select parameters) 1 option
     * 
     * @param array $where
     * @return string
     */
    private function getStrOneTypeWhere($where)
    {
        $result = false;
        if (!empty($where['='])) {
            $where['='][0] = $where['='][0];
            $result = $where['='][0]." = ".$where['='][1];
        } elseif (!empty($where['!='])) {
            $where['!='][0] = $where['='][0];
            $result = $where['!='][0]." != ".$where['!='][1];
        }
        return $result;
    }

    /**
     * getStrOneTypeWhere
     *  (ru) - соберёт строчку с условием определённого вида,
     *         для условий из массива $where (метода например select) 2 варианта
     * 
     *  (en) - will collect a line with a condition of a certain kind,
     *         for conditions from the array $where (for example, select parameters) 2 option
     * 
     * @param array $where
     * @return string
     */
    private function getStrTwoTypeWhere($where)
    {
        $result = '';
        if( !empty($where['AND'])) {
            foreach ($where['AND'] as $val) {
                $result .= ((!empty($result))? ' AND ': '').$this->getStrOneTypeWhere( $val );
            }
        } elseif (!empty($where['OR'])) {
            foreach ($where['OR'] as $val) {
                $result .= ((!empty($result))? ' OR ': '').$this->getStrOneTypeWhere($val);
            }
        }
        return $result;
    }

    /**
     * parseWhereForQuery - парсинг параметров where запроса
     *   массив будет в виде дерева, т.е. конечные массивы должны состоять из 2х элементов 
     * @param type $where
     * @param type $i
     * @param type $key2
     * @return type
     */    
    private function parseWhereForQuery($where)
    { 

        $strWhere = '';
        if ($this->isOneVersionWhere($where)) {
            // (ru) - если условия 1 варианта
            // (en) - if conditions 1 options
            $strWhere = $this->getStrOneTypeWhere($where);

        } elseif($this->isTwoVersionWhere($where)) {
            // (ru) - если условие 2 варианта
            // (en) - if condition 2 options
            $strWhere = $this->getStrTwoTypeWhere($where);
        } 
        // (ru) - остальное пока не поддерживается
        // (en) - the rest is not yet supported

        return $strWhere;
    }

     /**
     * selectDb - запрос типа select. на получение данных
     * @param $db - расурс, коннект к базе данных
     * @param string $tableName - имя таблицы 
     * @param array $columns - параметры (какие поля вернуть или * - все)
     * @param array $where - условия запроса, массив специальной структуры в виде дерева (может не быть)
     * @return - false or object result query
     */
    public function selectDb($tableName, $columns, $where = array())
    {
        $query = 'SELECT ';

        //$propertys = $this->allValueArrayInMbStrtolower($propertys);

        $strPropertys = implode(",", $columns);

        if (!empty($where)) {
            $where = ' WHERE '.$this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query .= $strPropertys.' FROM '.$tableName.$where.';';

        return  $this->query($query);
    }

    private static function allValueArrayInMbStrtolower($array)
    {

        foreach ($array as $key => $value) {
            $where[$key] = mb_strtolower($value);
        }
        return $where;
    }

    /**
     * insertDb - вставка, добавление новых строк в базу данных
     * @param string $tableName - имя таблицы 
     * @param array $propertys - параметры (поле = значение)
     * @return - false or object result query
     */
    public function insertDb($tableName, $propertys)
    {
        $query = '';

        // разбить параметры на два списка через запятую // TODO вынести куда то
        global $cryptoService;
        $nameProperty = '';
        $valueProperty = '';
        foreach ($propertys as $key=> $val) {
            $nameProperty .= (($nameProperty != '')? ', ': '').$key;

            if ($key == 'pass') {
                $val = md5($val.$cryptoService->getSole());
            }

            if (!is_numeric($val)) {
                $val = "'".$val."'";
            }

            $valueProperty .= (($valueProperty != '')? ', ': '').$val;
        }
        ////

        $query = "INSERT INTO ".$tableName." (".$nameProperty." ) VALUES(".$valueProperty.")";

        return  $this->query($query);
    }

    /**
     * updateDb - обновить поле таблицы
     * @param string $tableName - имя таблицы
     * @param array $propertys - параметры (поле = значение)
     * @param array $where - условия запроса, массив специальной структуры в виде дерева (может не быть)
     * @return - false or object result query
     */
    public function updateDb($tableName, $propertys, $where = array())
    {
        $query = 'UPDATE ';
        $textPropertys = '';
        global $cryptoService;
        foreach ($propertys as $key => $val) {

            if ($key == 'pass') {
                $val = md5($val.$cryptoService->getSole());
            }

            if (!is_numeric($val)) {
                $val = "'".$val."'";
            }
            $textPropertys .= ((!empty($textPropertys))? ',': '').' '.$key.'='.$val;
        }

        if (!empty($where)) {
            $where = ' WHERE '.$this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query .= $tableName.' SET '.$textPropertys.$where.';';

        return  $this->query($query);
    }

    /**
     * createTable - создать таблицу в базе данных
     * @param string $tableName - имя таблицы
     * @param array $columnDefinitions - параметры (приер  login varchar(50), name varchar(50) ...)
     * @return - false or object result query
     */
    public function createTable($tableName, $columnDefinitions)
    {
        $query = '';
        $textPropertys = '';

        foreach ($columnDefinitions as $val) {
            $strPos = strpos($val, 'int PRIMARY KEY AUTO_INCREMENT');
            if ($strPos !== false) {
                $val = str_replace('int PRIMARY KEY AUTO_INCREMENT', 'SERIAL PRIMARY KEY', $val);
            }
            $textPropertys .= ((!empty($textPropertys))? ',': '').' '.$val;
        }
        $query = 'CREATE TABLE IF NOT EXISTS '.$tableName.' ('.$textPropertys.');';

        return  $this->query($query);
    }

    /**
     * deleteDb - удаление строк из таблицы
     * @param string $tableName - имя таблицы
     * @param array $where - условия запроса, что удалять
     * @return boolean
     */
    public function deleteDb($tableName, $where)
    {
        $query = '';
        if (!empty($where)) {
            $where = ' WHERE '.$this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query = 'DELETE FROM '.$tableName.$where;

        return $this->query($query);
    }

    public function __destruct() 
    {
        if (!empty($this->db)) {
            $this->close($this->db);
        }
    }
}
