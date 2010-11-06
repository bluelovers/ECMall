<?php

define('APP_ROOT', dirname(__FILE__));
define('ROOT_PATH', dirname(APP_ROOT));

include(ROOT_PATH . '/eccore/ecmall.php');

ecm_define(ROOT_PATH . '/data/config.inc.php');
define('UPGRADING', isset($_GET['upgrading']));

ECMall::Startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  APP_ROOT . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/ecapp.base.php',
        APP_ROOT . '/includes/upgrader.base.php'
    ),
));

?>
