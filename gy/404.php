<?php include $_SERVER["DOCUMENT_ROOT"]."/gy/gy.php"; // подключить ядро // include core 

use Gy\Core\Localization;

global $APP;
global $USER;

$localization = new Localization(
    $APP->urlProject."/gy/lang", 
    '404', 
    $APP->options['lang']
);

http_response_code(404);
?>

<div><?=$localization->getMessage('error')?></div>
   
<?php
die();
