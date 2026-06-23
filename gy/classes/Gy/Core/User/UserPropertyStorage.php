<?php

namespace Gy\Core\User;

if (!defined("GY_CORE") && (GY_CORE !== true)) {
    die("gy: err include core");
}

/**
 * GeneralUsersPropertys - класс для работы с общими свойствами пользователей
 */
class UserPropertyStorage
{

    private static $tableNameCreatePropertys = 'create_all_users_property';
    private static $tableNameTypePropertys = 'type_all_user_propertys';

    private static $tableNameTypePropertysForCodeTypeProperty = [
        'text' => 'value_all_user_propertys_text',
    ];

    /**
     * getAllGeneralUsersPropertys
     *  - получить все созданные пользовательские свойства
     *
     * @return array
     * @global type $DB
     */
    public static function getAllGeneralUsersPropertys()
    {
        global $DB;
        $res = $DB->selectDb(
            self::$tableNameCreatePropertys,
            ['*'],
            [],
        );
        $result = $DB->fetchAll($res, 'id');
        return $result;
    }

    /**
     * getAllTypeAllUsersPropertys
     *  - получить все возможные типы пользовательских свойств
     *
     * @return array
     * @global type $DB
     */
    public static function getAllTypeAllUsersPropertys()
    {
        global $DB;
        $res = $DB->selectDb(
            self::$tableNameTypePropertys,
            ['*'],
            [],
        );
        return $DB->fetchAll($res, 'id');
    }

    /**
     * addUsersPropertys
     *  - создать пользовательское свойство
     *
     * @param string $name - имя
     * @param int $idType - тип
     * @param string $code - код
     * @return boolean
     * @global type $DB
     */
    public static function addUsersPropertys($name, $idType, $code)
    {
        $result = false;

        global $DB;
        $res = $DB->insertDb(
            self::$tableNameCreatePropertys,
            [
                'name_property' => $name,
                'type_property' => $idType,
                'code' => $code,
            ],
        );

        if ($res) {
            $result = true;
        }
        return $result;
    }

    /**
     * deleteUserProperty
     *  - удалить общее пользовательское свойство
     *
     * @param int $id - id общего пользовательского свойства
     * @return boolean
     * @global type $DB
     */
    public static function deleteUserProperty($id)
    {
        $result = false;
        global $DB;

        $res = $DB->deleteDb(
            self::$tableNameCreatePropertys,
            ['=' => ['id', $id]],
        );

        if ($res) {
            $result = true;
        }

        //удалить значения
        self::deleteAllValuesAllUserBypropertyId($id, 'text'); // text - т.к. пока других нет

        return $result;
    }

    /**
     * deleteAllValuesAllUserBypropertyId
     *  - удалить все значения определённого свойства у всех пользователей
     *
     * @param int $idProperty - id свойства (общее свойство)
     * @param string $typePropertyCode - пока у всех значение text
     * @return boolean
     * @global type $DB
     */
    public static function deleteAllValuesAllUserBypropertyId($idProperty, $typePropertyCode)
    {
        $result = false;

        if (!empty(self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode])) {
            global $DB;

            $res = $DB->deleteDb(
                self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode],
                ['=' => ['id_property', $idProperty]],
            );

            if ($res) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * getAllValueUserProperty
     *  - взять все значения определённого типа свойства пользователя
     *
     * @param int $idUser - id пользователя
     * @param string $typePropertyCode - пока у всех значение text
     * @return boolean/array
     * @global type $DB
     */
    public static function getAllValueUserProperty($idUser, $typePropertyCode)
    {
        $result = false;

        if (!empty(self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode])) {
            global $DB;
            $res = $DB->selectDb(
                self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode],
                ['*'],
                ['=' => ['id_users', $idUser]],
            );
            $result = $DB->fetchAll($res, 'id_property');
        }
        return $result;
    }

    /**
     * addValueProperty
     *  - добавить значение свойства
     *
     * @param int $idUser - id пользователя
     * @param string $typePropertyCode - пока у всех значение text
     * @param int $idProperty - id пользовательского свойства
     * @param string $value - пока тип text, тут только строка
     * @return boolean
     * @global type $DB
     */
    public static function addValueProperty($idUser, $typePropertyCode, $idProperty, $value)
    {
        $result = false;

        if (!empty(self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode])) {
            global $DB;
            $res = $DB->insertDb(
                self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode],
                [
                    'value' => $value,
                    'id_users' => $idUser,
                    'id_property' => $idProperty,
                ],
            );

            if ($res) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * deleteValueProperty
     *  - удалить значения конкретного свойства конкретного пользователя
     *
     * @param int $idUser - id пользователя
     * @param string $typePropertyCode - пока у всех значение text
     * @param int $idProperty - id пользовательского свойства
     * @return boolean
     * @global type $DB
     */
    public static function deleteValueProperty($idUser, $typePropertyCode, $idProperty)
    {
        $result = false;

        if (!empty(self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode])) {
            global $DB;

            $res = $DB->deleteDb(
                self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode],
                [
                    'AND' => [
                        ['=' => ['id_users', $idUser]],
                        ['=' => ['id_property', $idProperty]],
                    ],
                ],
            );

            if ($res) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * updateValueProperty
     *  - изменить значение конкретного свойства конкретного пользователя
     *
     * @param int $idUser - id пользователя
     * @param string $typePropertyCode - пока у всех значение text
     * @param int $idProperty - id пользовательского свойства
     * @param string $value - пока тип text, тут только строка
     * @return boolean
     * @global type $DB
     */
    public static function updateValueProperty($idUser, $typePropertyCode, $idProperty, $value)
    {
        $result = false;

        if (!empty(self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode])) {
            global $DB;
            $res = $DB->updateDb(
                self::$tableNameTypePropertysForCodeTypeProperty[$typePropertyCode],
                [
                    'id_users' => $idUser,
                    'id_property' => $idProperty,
                    'value' => $value,
                ],
                [
                    'AND' => [
                        ['=' => ['id_users', $idUser]],
                        ['=' => ['id_property', $idProperty]],
                    ],
                ],
            );

            if ($res) {
                $result = true;
            }
        }
        return $result;
    }

}
