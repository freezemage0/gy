<?php

namespace Gy\Core\User;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/**
 * AccessUserGroup - будет всё что связано с правами доступов пользователей 
 * (и групп пользователей)
 */
class AccessUserGroup
{

    private static $tableNameAccessGroup = 'access_group';
    private static $tableNameUserActions = 'action_user';
    private static $tableNameUsersInGroupss = 'users_in_groups';

    private static $cacheTimeGetData = 604_800;

    /**
     * checkAccessUserGroupsByUserAction - определить можно ли пользователю 
     *     с заданным набором его групп 
     *     и данными по всем группам выполнить указанное действие 
     * 
     * @param array $groupsThisUser - группы к каким относится пользователь
     * @param array $dataAllGroups - данные по всем группам
     * @param string $thisAction - проверяемое действие пользователя
     * @return boolean
     */
    private static function checkAccessUserGroupsByUserAction(
        $groupsThisUser, 
        $dataAllGroups, 
        $thisAction
    ) {
        $arResult = false;

        // определить все действия разрешённые для данного пользователя
        $AllAccessActionsThisUser = array();
        foreach ($groupsThisUser as $nameGroup) {
            if ($dataAllGroups[$nameGroup]) {
                $AllAccessActionsThisUser = array_merge(
                    $AllAccessActionsThisUser, 
                    $dataAllGroups[$nameGroup]['code_action_user']
                );
            }
        }
        
        // найти заданное действие среди разрешённых для данного пользователя
        // либо проверить на админа (т.е. разрешены любые действия)
        if (in_array($thisAction , $AllAccessActionsThisUser) 
            || in_array('action_all' , $AllAccessActionsThisUser)
        ) {
            $arResult = true;
        }
        return $arResult;
    }

    /**
     * accessUser() - проверит разрешёно ли указанное действие заданному 
     *     пользователю
     * 
     * @param int $userId - id пользователя
     * @param string $actionUser - код пользовательского действия
     * @return boolean
     */
    public static function accessUser($userId, $actionUser)
    {

        // получить данные по пользователю 
        global $USER;
        $dataUserFind = $USER->getUserById($userId);
        
        // получить данные по всем группам
        $dataAllGroups = self::getAccessGroup();
        
        // определить пользователю с таким набором групп доступно ли указанное 
        //     действие
        return self::checkAccessUserGroupsByUserAction(
            $dataUserFind['groups'], 
            $dataAllGroups, 
            $actionUser
        );
    }

    /**
     * accessThisUserByAction - проверить разрешено ли текущему пользователю 
     *   указанное действие
     * 
     * @global type $USER
     * @param string $action - код действия 
     * @return boolean
     */
    public static function accessThisUserByAction($action)
    {
        global $USER;
        $groupsThisUser = $USER->getThisUserGroups();
        
        // получить данные по всем группам
        $dataAllGroups = self::getAccessGroup();

        // определить пользователю с таким набором групп доступно ли указанное 
        //     действие
        return self::checkAccessUserGroupsByUserAction(
            $groupsThisUser,
            $dataAllGroups,
            $action
        );
    }

    /**
     * getAccessGroup() - получить все группы пользователей какие есть
     *  + вернутся заданные в группах разрешения на пользовательские действия
     * @return array
     */
    public static function getAccessGroup()
    {
        $arResult = array();

        global $APP;
        global $CACHE_CLASS_NAME;
        $cache = new $CACHE_CLASS_NAME($APP->url);
        $initCache = $cache->cacheInit('getAccessGroup', self::$cacheTimeGetData);

        if ($initCache) {
            $arResult = $cache->getCacheData();
        } else {

            global $DB;
            $res = $DB->selectDb(self::$tableNameAccessGroup, array('*'));
            while ($arRes = $DB->fetch($res)) {
                if (!empty($arRes['code_action_user'])) {
                    $arResult[$arRes['code']]['code_action_user'][$arRes['code_action_user']] = $arRes['code_action_user'];
                }
                $arResult[$arRes['code']]['name'] = $arRes['name'];
                $arResult[$arRes['code']]['code'] = $arRes['code'];
                $arResult[$arRes['code']]['text'] = $arRes['text'];
            }

            $cache->setCacheData($arResult);
        }

        return $arResult;
    }

    /**
     * clearCacheForFunctionGetAccessGroup -
     *  сбросить кеш на получение разрешений для групп и всех данных по группам
     * 
     * @global type $APP
     * @global type $CACHE_CLASS_NAME
     */
    public static function clearCacheForFunctionGetAccessGroup()
    {
        global $APP;
        global $CACHE_CLASS_NAME;
        $cache = new $CACHE_CLASS_NAME($APP->url);
        $cache->cacheInit('getAccessGroup', self::$cacheTimeGetData);
        $cache->clearThisCache();
    }

    /**
     * getUserAction() - получить все какие есть пользовательские действия
     * @return array
     */
    public static function getUserAction()
    {
        $arResult = array();

        global $APP;
        global $CACHE_CLASS_NAME;
        $cache = new $CACHE_CLASS_NAME($APP->url);
        $initCache = $cache->cacheInit(
            'getUserAction', 
            self::$cacheTimeGetData
        );

        if ($initCache) {
            $arResult = $cache->getCacheData();
        } else {

            global $DB;
            $res = $DB->selectDb(self::$tableNameUserActions, array('*'));
            while ($arRes = $DB->fetch($res)) {
                $arResult[$arRes['code']]['code'] = $arRes['code'];
                $arResult[$arRes['code']]['text'] = $arRes['text'];
            }

            $cache->setCacheData($arResult);
        }

        return $arResult;
    }

    /**
     * getListGroupsByUser() - получить список групп к каким относится 
     *     пользователь
     * 
     * @param int $idUsers - id пользователя
     * @return array
     */
    public static function getListGroupsByUser($idUsers)
    {
        $arResult = array();

        // определить id групп к каким относится пользователь
        global $DB;
        $res = $DB->selectDb(
            self::$tableNameUsersInGroupss, 
            array('code_group'), 
            array('='=>array('id_user', $idUsers ))
        );
        while ($arRes = $DB->fetch($res)) {
            $arResult[$arRes['code_group']] = $arRes['code_group'];
        }

        return $arResult;
    }

    /**
     * addUserInGroup() - добавить пользователя в группуы
     * @param int $idUsers - id пользователя
     * @param string $codeGroup - код группы
     * @return boolean
     */
    public static function addUserInGroup($idUsers, $codeGroup)
    {
        $arResult = false;
        global $DB;
        $res = $DB->insertDb(
            self::$tableNameUsersInGroupss,
            array(
                'code_group' => $codeGroup,
                'id_user' => $idUsers,
            )
        );
        if ($res) {
            $arResult = true;
        }
        return $arResult;
    }

    /**
     * deleteUserInAllGroups - удалить пользователя из всех групп 
     *  (где он состоит)
     * @param int $idUsers - id пользователя
     * @return boolean
     */
    public static function deleteUserInAllGroups($idUsers)
    {
        $arResult = false;
        global $DB;
        $res = $DB->deleteDb(
            self::$tableNameUsersInGroupss, 
            array('=' => array('id_user', $idUsers)) 
        );
        if ($res) {
            $arResult = true;
        }
        return $arResult;
    }

    /**
     * deleteAllActionsForGroup()
     * - удалить все заданные, разрешённые действия пользователей для указанной 
     *      группы
     * 
     * @global type $DB
     * @param string $codeUserGroup
     * @return boolean
     */
    public static function deleteAllActionsForGroup($codeUserGroup)
    {
        $arResult = false;
        global $DB;
        $dataAllGroup = self::getAccessGroup();

        if (!empty($dataAllGroup[$codeUserGroup])) {
            // тут будут данные по нужной группе
            $dataThisGroup = $dataAllGroup[$codeUserGroup];
            $dataThisGroup['code_action_user'] = '';

            // удаляем все данные по этой группе из БД
            $res = $DB->deleteDb(
                self::$tableNameAccessGroup, 
                array(
                    '=' => array(
                        'code', 
                        "'".$codeUserGroup."'"
                    )
                ) 
            );

            if ($res) {
                // добавляем пустую группу
                $res2 = $DB->insertDb(
                    self::$tableNameAccessGroup,
                    $dataThisGroup
                );
                if ($res2) {
                    $arResult = true;
                }
            }
        }
    }

    /**
     * addOptionsGroup() 
     * - добавить для указанной группы пользователей разрешённое действие
     *  
     * @global type $DB
     * @param string $codeUserGroup - код группы
     * @param string $codeAction - код пользовательского действия
     * @return boolean
     */
    public static function addOptionsGroup($codeUserGroup, $codeAction)
    {
        $arResult = false;
        global $DB;
        $dataAllGroup = self::getAccessGroup();

        if (!empty($dataAllGroup[$codeUserGroup])) {
            $dataThisGroup = $dataAllGroup[$codeUserGroup];

            // если действий для пользователя нет обновить группу 
            //     (добавить действия)
            if (empty($dataThisGroup['code_action_user'])) {
                $dataThisGroup['code_action_user'] = $codeAction;
                // если мы попали сюда то всего одна запись в БД соответствует 
                //     этой группе её и обновляем
                $res = $DB->updateDb(
                    self::$tableNameAccessGroup,
                    $dataThisGroup,
                    array(
                        '=' => array(
                            'code',
                            "'".$codeUserGroup."'"
                        )
                    )
                );
                if ($res) {
                    $arResult = true;
                }
            } else { // добавить копию группы с новым действием
                $dataThisGroup['code_action_user'] = $codeAction;
                $res = $DB->insertDb(
                    self::$tableNameAccessGroup, 
                    $dataThisGroup
                );
                if ($res) {
                    $arResult = true;
                }
            }

        }

        // сбросить кеш на получение разрешений для групп и всех данных по группам
        self::clearCacheForFunctionGetAccessGroup();

        return $arResult;
    }

    /**
     * addUserGroup
     *  - добавить новую пользовательскую группу 
     * 
     * @global type $DB
     * @param array $arDataNewGroup - массив с данными по группе (name, code, text)
     * @param array $arActionUserThisGroup - массив с разрешёнными для этой группы 
     *     действиями
     * @return boolean
     */
    public static function addUserGroup($arDataNewGroup, $arActionUserThisGroup)
    {
        global $DB;
        $arResult = true;
        foreach ($arActionUserThisGroup as $value) {
            $res = $DB->insertDb(
                self::$tableNameAccessGroup,
                array(
                    'code' => $arDataNewGroup['code'],
                    'name' => $arDataNewGroup['name'],
                    'text' => $arDataNewGroup['text'],
                    'code_action_user' => $value
                )
            );
            if ($res) {
                //$arResult = true;
            } else {
                $arResult = false;
            }
        }

        // сбросить кеш на получение разрешений для групп и всех данных по группам
        self::clearCacheForFunctionGetAccessGroup();

        return $arResult;
    }

    /**
     * deleteUserGroupByCode
     *  - удалить пользовательскую группу по коду группы
     * 
     * @global type $DB
     * @param string $codeGroup - код удаляемой группы
     * @return boolean
     */
    public static function deleteUserGroupByCode($codeGroup)
    {
        global $DB;
        $arResult = false;
        // удалить все связи пользователей с этой группой

        $res = $DB->deleteDb(
            self::$tableNameUsersInGroupss,
            array('=' => array('code_group' , "'".$codeGroup."'" ) )
        );
        if ($res) {
            $arResult = true;
        }

        // удалить группу по коду уруппы
        if ($arResult) {
            $arResult = false;
            $res = $DB->deleteDb(
                self::$tableNameAccessGroup,
                array('=' => array('code' , "'".$codeGroup."'" ) )
            );
            if ($res) {
                $arResult = true;
            }
        }

        // сбросить кеш на получение разрешений для групп и всех данных по группам
        self::clearCacheForFunctionGetAccessGroup();

        return $arResult;
    }

}
