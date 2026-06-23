<?php

global $argv;
$isRunConsole = php_sapi_name() === 'cli';
global $BR;
$BR = "\n";

//print_r($argv);

function showHelpFromInstall(): void {
    global $BR;
    echo $BR."This script set options for gy framework".$BR;
    echo "===================================".$BR;
    echo ">php -f consoleInstallOptions.php <options>".$BR;
    echo $BR;
    echo "options:".$BR;
    echo "    help  - show help this script".$BR;
    echo "    set-all <array options> - set all options (clear options if not input)".$BR;
    echo "    set-option <array options> - set options (save old options if not input)".$BR;
    echo $BR;
    echo "  example: php -f consoleInstallOptions.php set-all sole 111 db_type mysql".$BR;
    echo "  example: php -f consoleInstallOptions.php set-option sole 111 db_type mysql".$BR;
    echo "  example: php -f consoleInstallOptions.php help".$BR;
    echo $BR;
    echo $BR;
    echo $BR;
}


function parseOprions($optionsFromConsole){
    $arOptions = array();
    for ($i = 2; $i < (count($optionsFromConsole)-1); $i = $i+2) {
        if (empty($optionsFromConsole[$i+1])) {
            $optionsFromConsole[$i+1] = '';
        }

        if ($optionsFromConsole[$i+1] == '***') {
            $optionsFromConsole[$i+1] = '';
        }

        if (strripos($optionsFromConsole[$i], 'db') !== false) {
            $arOptions['db_config'][$optionsFromConsole[$i]] = $optionsFromConsole[$i+1];

        } else {
            $arOptions[$optionsFromConsole[$i]] = $optionsFromConsole[$i+1];
        }
    }

    return $arOptions;
}

function createTextForFileCofig($options){
    global $BR;
    $fileText = '';

    if (!empty($options)) {

        $fileText = '<?php '.$BR.'
if (!defined("GY_CORE") && GY_CORE !== true ) die("err_core");'.$BR.'

$gyConfig = array('.$BR;

        foreach ($options as $key => $val) {

            if (!is_array($val)) {
                $fileText .= '    "'.$key.'" => "'.$val.'",'.$BR;
            } else {
                $fileText .= '    "'.$key.'" => array('.$BR;
                foreach ($val as $key2 => $val2) {
                    $fileText .= '        "'.$key2.'" => "'.$val2.'",'.$BR;
                }
                $fileText .= '    ),'.$BR;
            }

        }
        $fileText .= ');'.$BR;
    }

    return $fileText;
}

if ($isRunConsole) { // пока запускать только из консоли
    if (empty($argv[1]) || ($argv[1] == 'help')) {
        showHelpFromInstall();
    } elseif ($argv[1] == 'set-all') {
        echo 'run set-all'.$BR;

        $options = parseOprions($argv);

        if (!empty($options)) {
            $file = fopen(__DIR__.'/../config/gy_config.php', 'w');
            fwrite($file, createTextForFileCofig($options) );
            fclose($file);
        }
        echo 'finish set-all'.$BR;

    } elseif ($argv[1] == 'set-option') {
        echo 'run set-option'.$BR;
        $options = parseOprions($argv);

        include __DIR__."/../gy.php";
        $oldOptions = $APP->options;

        //print_r($oldOptions);

        foreach ($options as $key => $val) {
            if (is_array($val)) {
                //$tempArr = $oldOptions[$key];
                foreach ($val as $key2 => $val2) {
                    $oldOptions[$key][$key2] = $val2;
                }
            } else {
                $oldOptions[$key] = $val;
            }
        }

        if (!empty($oldOptions)) {
            $file = fopen(__DIR__.'/../config/gy_config.php', 'w');
            fwrite($file, createTextForFileCofig($oldOptions) );
            fclose($file);
        }
        echo 'finish set-option'.$BR;

    }

} else {
    echo '! Error. You need to run the script in the console';

}
