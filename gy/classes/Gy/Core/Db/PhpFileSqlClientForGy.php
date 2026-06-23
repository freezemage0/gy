<?php

namespace Gy\Core\Db;

use Gy\Core\AbstractClasses\Db;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/* PhpFileSqlClientForGy - класс для работы с базой данных PhpFileSql
 *   https://github.com/ssv32/PhpFileSql
 * class work PhpFileSql 
 */
class PhpFileSqlClientForGy extends Db
{

    public $test = 'PhpFileSqlClient ok';
    public $db; //TODO private

    // даныне после запроса селект для метода fetch()
    private $dataSelectForFetch = array();  

    /**
     * clearResultMethodSelect()
     *  - сбросит результаты запроса метода select
     * 
     * @return boolean
     */
    private function clearResultMethodSelect()
    {
        $this->dataSelectForFetch = array();
        return true;
    }

    /* connect() - create connect in database
    * @param $host
    * @param $user
    * @param $pass 
    * @param $nameDb
    * @param $port - не используется
    * @return resurs, false
    */
    public function connect($dir, $login, $pass, $nameDb, $port = false)
    {
        $phpFileSql = new \PhpFileSql($dir);
        $phpFileSql->connect($login, $pass, $nameDb);
        
        $this->db = $phpFileSql;
        return $this;
        
    }

    /* query()  - out query in database //TODO
     * @param $db - resurs (create self::connect()), $query - string query
     * @return true - ok OR false - not ok
     */
    public function query($query)
    {	
        // 
    }

    /*  close() - close connect database
     * @param $db - resurs (create self::connect()) 
     * @return true - ok OR false - not ok
     */
    public function close()
    {
        if (!empty($this->db)) {
            $phpFileSql = $this->db;
            return $phpFileSql->close();
        } else {
            return false;
        }
    }

    /** 
     * fetch - получить порцию (строку) данных, после выполнения запроса в БД
     * @param $res - результат отработки запроса в БД
     * @return array
     */
    public function fetch($res)
    {
        $res = $this->dataSelectForFetch;
        
        $result = false;
        if (($res !== false) && is_array($res)) {

            // беру первое значение из него
            $result = array_shift($res);

            // записываем без первого значения
            $this->dataSelectForFetch = $res;

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
        $result = false;

        if (($res !== false) && is_array($res)) {
            if ($key !== false ) {
                foreach ($res as $value) {
                    if (!empty($value[$key])) {
                        $result[$value[$key]] = $value;
                    }
                }
            } else {
                $result = $res;
            }
        }

        return $result;
    }

    public function __construct($dbConfig)
    {
        if ( empty($this->db)) {
            if (!empty($dbConfig)) {
                $this->connect($dbConfig['db_url'], $dbConfig['db_user'], $dbConfig['db_pass'], $dbConfig['db_name']);
            }
        }
    }

     /** //TODO
     * selectDb - запрос типа select. на получение данных
     * @param $db - расурс, коннект к базе данных
     * @param string $tableName - имя таблицы 
     * @param array $propertys - параметры (какие поля вернуть или * - все)
     * @param array $where - условия запроса, массив специальной структуры в виде дерева (может не быть)
     * @return - false or object result query
     */
    public function selectDb($tableName, $propertys = '*', $where = false)
    {

        // чуть подправить для совместимости
        if ($propertys[0] == '*') {
            $propertys = '*';
        }

        // подготовить массив с условиями для класса PhpFileSql
        $where = $this->createTrueArrayWhereFromPhpFileSql($where);

        $dataResult = $this->db->select($tableName, $propertys, $where);

        // записываю для метода fetch()
        $this->dataSelectForFetch = $dataResult;

        return $dataResult;
    }

    /**
     * insertDb - вставка, добавление новых строк в базу данных
     * @param string $tableName - имя таблицы 
     * @param array $propertys - параметры (поле = значение)
     * @return - false or object result query
     */
    public function insertDb($tableName, $propertys)
    {  
        // сбросить данные предыдущего вызова метода select
        $this->clearResultMethodSelect();

        global $cryptoService;

        // если встречается пароль то засолить и зашифровать его
        if (!empty($propertys['pass'])) {
            $propertys['pass'] = md5($propertys['pass'].$cryptoService->getSole());
        }

        return  $this->db->insertInto($tableName, $propertys);
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
        // сбросить данные предыдущего вызова метода select
        $this->clearResultMethodSelect();

        // подготовить массив с условиями для класса PhpFileSql
        $where = $this->createTrueArrayWhereFromPhpFileSql($where);

        // если встречается пароль то засолить и зашифровать его
        global $cryptoService;
        if (!empty($propertys['pass'])) {
            $propertys['pass'] = md5($propertys['pass'].$cryptoService->getSole());
        }

        return $this->db->update($tableName, $propertys, $where);
    }

    /** // TODO сделать PRIMARY KEY AUTO_INCREMENT
     * createTable - создать таблицу в базе данных
     * @param string $tableName - имя таблицы
     * @param array $propertys - параметры (приер  login varchar(50), name varchar(50) ...) 
     * @return - false or object result query
     */
    public function createTable($tableName, $propertys)
    {
        // сбросить данные предыдущего вызова метода select
        $this->clearResultMethodSelect();

        // массив мараметров подходящий для PhpFileSql метода createTable
        $arrayColumns = array();

        // нужно подогнать свойства под метод класса PhpFileSql
        foreach ($propertys as $val) {
            $attr = explode(' ', $val);
            if( (count($attr)>2) 
                && ($attr[1] == 'int' )
                && ($attr[2] == 'PRIMARY')
                && ($attr[3] == 'KEY')
                && ($attr[4] == 'AUTO_INCREMENT')
            ) { 
                // PRIMARY KEY AUTO_INCREMENT
                $arrayColumns[] = array($attr[0], 'PRIMARY_KEY_AUTO_INCREMENT' );
            } else {
                $arrayColumns[] = $attr[0];
            }
        }

        return $this->db->createTable($tableName, $arrayColumns);
    }

    /** //TODO из за условий может работать не на всём, желательно ещё потестировать
     * deleteDb - удаление строк из таблицы
     * @param string $tableName - имя таблицы
     * @param array $where - условия запроса, что удалять
     * @return boolean
     */
    public function deleteDb($tableName, $where)
    {
        // сбросить данные предыдущего вызова метода select
        $this->clearResultMethodSelect();
        
        // подготовить массив с условиями для класса PhpFileSql
        $where = $this->createTrueArrayWhereFromPhpFileSql($where);
        
        return $this->db->delete($tableName, $where);
    }

    /**
     * createTrueArrayWhereFromPhpFileSql
     *  - сделать массив where к виду подходящему для класса PhpFileSql
     * 
     * @param array $where
     * @return array
     */
    public function createTrueArrayWhereFromPhpFileSql($where)
    {

        if (is_array($where)) {
            foreach ($where as $key0 => $value0) {
                if (in_array($key0, array('=', '!='))) {
                    $where[$key0][1] = str_replace("'", '', $where[$key0][1]);
                } elseif (in_array($key0, array('AND', 'OR'))) {
                    foreach ($value0 as $key1 => $value1) {
                        foreach ($value1 as $key2 => $value2) {
                            $where[$key0][$key1][$key2][1] = str_replace("'", '', $where[$key0][$key1][$key2][1]);
                        } 
                    }
                }
            } 
        }  

        return $where;
    }

    public function __destruct()
    {
        $this->close();
    }
}
