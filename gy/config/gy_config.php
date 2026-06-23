<?php

if (!defined("GY_CORE") && GY_CORE !== true) die("err_core");

return [
        'lang'            => 'rus',
        'sole'            => 'pass1021asz#_@)A',
        'db_config'       => [
                'db_type' => 'MySql',
                'db_host' => 'localhost',
                'db_user' => 'mysql',
                'db_pass' => 'mysql',
                'db_name' => 'gy-test',
                'db_port' => '31006',
        ],
        'urlPage404'      => 'gy/404.php',
    //"secretKeyAuthorizationAdminPanel" => "0GJ9Hs7VRXfp0x0DJWAlxBmfKJPGG8qAlwm6l5JoJ4smB2",
        'type_cache'      => 'CacheFiles',
        'v-gy'            => '0.3-alpha',
        'dir_public_file' => 'public',
];
