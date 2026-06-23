<?php

namespace Gy\Core\Storage\Mysql;

use Gy\Core\Configuration;
use Gy\Core\Container;
use Gy\Core\Db\type;
use Gy\Core\Storage\Driver;

if (!defined("GY_CORE") && (GY_CORE !== true)) {
    die("gy: err include core");
}

/* MySql - класс для работы с базой данных mysql
 * class work mysql 
 */

class MySql extends Driver
{
    /** @var resource|null */
    private mixed $db = null;

    /* connect() - create connect in database
    * @param $host
    * @param $user
    * @param $pass
    * @param $nameDb
    * @param $port
    * @return resurs, false
    */
    public function __construct(
        private readonly Configuration $configuration,
    ) {}

    public function connect($host, $user, $pass, $nameDb, $port)
    {
        return $this->db = mysqli_connect($host, $user, $pass, $nameDb, $port);
    }
    /* query()  - out query in database
     * @param $db - resurs (create self::connect()), $query - string query
     * @return true - ok OR false - not ok
     */

    public function query($query): \mysqli_result|bool
    {
        return mysqli_query($this->driver(), $query);
    }
    /*  close() - close connect database
     * @param $db - resurs (create self::connect())
     * @return true - ok OR false - not ok
     */

    public function close(): bool
    {
        if ($this->db === null) {
            return true;
        }

        return mysqli_close($this->db);
    }

    /**
     * fetch - получить порцию (строку) данных, после выполнения запроса в БД
     * @param $res - результат отработки запроса в БД
     * @return array
     */
    public function fetch($res)
    {
        $result = [];
        if ($res !== false) {
            $result = mysqli_fetch_assoc($res);
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
        $result = [];
        while ($arRes = self::fetch($res)) {
            if ($key !== false) {
                $result[$arRes[$key]] = $arRes;
            } else {
                $result[] = $arRes;
            }
        }
        return $result;
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
                if (in_array($key, ['=', '!=']) && (count($value) == 2)) {
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
            if (in_array($key, ['OR', 'AND'])) {
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
            $result = $where['='][0] . " = " . $where['='][1];
        } elseif (!empty($where['!='])) {
            $result = $where['!='][0] . " != " . $where['!='][1];
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
        if (!empty($where['AND'])) {
            foreach ($where['AND'] as $val) {
                $result .= ((!empty($result)) ? ' AND ' : '') . $this->getStrOneTypeWhere($val);
            }
        } elseif (!empty($where['OR'])) {
            foreach ($where['OR'] as $val) {
                $result .= ((!empty($result)) ? ' OR ' : '') . $this->getStrOneTypeWhere($val);
            }
        }
        return $result;
    }

    /**
     * parseWhereForQuery - парсинг параметров where запроса
     *   массив будет в виде дерева, т.е. конечные массивы должны состоять из 2-х элементов
     * @param array $where
     * @return string
     */
    private function parseWhereForQuery(array $where): string
    {
        $strWhere = '';
        if ($this->isOneVersionWhere($where)) {
            // (ru) - если условия 1 варианта
            // (en) - if conditions 1 options
            $strWhere = $this->getStrOneTypeWhere($where);
        } elseif ($this->isTwoVersionWhere($where)) {
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
    public function selectDb(string $tableName, array $columns, array $where = [])
    {
        $query = 'SELECT ';
        $strProperties = implode(",", $columns);

        if (!empty($where)) {
            $where = ' WHERE ' . $this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query .= $strProperties . ' FROM ' . $tableName . $where . ';';

        return $this->query($query);
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
        $cryptoService = Container::getInstance()->getCryptoService();

        $nameProperty = '';
        $valueProperty = '';
        foreach ($propertys as $key => $val) {
            $nameProperty .= (($nameProperty != '') ? ', ' : '') . $key;

            if ($key == 'pass') {
                $val = md5($val . $cryptoService->getSalt());
            }

            if (!is_numeric($val)) {
                $val = "'" . $val . "'";
            }

            $valueProperty .= (($valueProperty != '') ? ', ' : '') . $val;
        }
        ////

        $query = "INSERT INTO " . $tableName . " (" . $nameProperty . " ) VALUES(" . $valueProperty . ")";

        return $this->query($query);
    }

    /**
     * updateDb - обновить поле таблицы
     * @param string $tableName - имя таблицы
     * @param array $propertys - параметры (поле = значение)
     * @param array $where - условия запроса, массив специальной структуры в виде дерева (может не быть)
     * @return - false or object result query
     */
    public function updateDb($tableName, $propertys, $where = [])
    {
        $query = 'UPDATE ';
        $textPropertys = '';

        $cryptoService = Container::getInstance()->getCryptoService();
        foreach ($propertys as $key => $val) {
            if ($key == 'pass') {
                $val = md5($val . $cryptoService->getSole());
            }

            if (!is_numeric($val)) {
                $val = "'" . $val . "'";
            }
            $textPropertys .= ((!empty($textPropertys)) ? ',' : '') . ' ' . $key . '=' . $val;
        }

        if (!empty($where)) {
            $where = ' WHERE ' . $this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query .= $tableName . ' SET ' . $textPropertys . $where . ';';

        return $this->query($query);
    }

    /**
     * createTable - создать таблицу в базе данных
     * @param string $tableName - имя таблицы
     * @param array $propertys - параметры (приер  login varchar(50), name varchar(50) ...)
     * @return - false or object result query
     */
    public function createTable($tableName, $propertys)
    {
        $query = '';
        $textPropertys = '';
        foreach ($propertys as $val) {
            $textPropertys .= ((!empty($textPropertys)) ? ',' : '') . ' ' . $val;
        }

        $query = 'CREATE TABLE ' . $tableName . ' (' . $textPropertys . ');';

        return $this->query($query);
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
            $where = ' WHERE ' . $this->parseWhereForQuery($where);
        } else {
            $where = '';
        }

        $query = 'DELETE FROM ' . $tableName . $where;

        return $this->query($query);
    }

    public function __destruct()
    {
        $this->close();
    }

    private function driver(): mixed
    {
        if ($this->db !== null) {
            return $this->db;
        }

        if (empty($this->configuration->databaseConfig['db_port'])) {
            $this->configuration->databaseConfig['db_port'] = ini_get("mysqli.default_port");
        }
        return $this->connect(
            $this->configuration->databaseConfig['db_host'],
            $this->configuration->databaseConfig['db_user'],
            $this->configuration->databaseConfig['db_pass'],
            $this->configuration->databaseConfig['db_name'],
            $this->configuration->databaseConfig['db_port'],
        );
    }
}
