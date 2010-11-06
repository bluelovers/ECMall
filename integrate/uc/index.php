<?php

/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));
define('ROOT_PATH', dirname(dirname(APP_ROOT)));   //该常量是ECCore要求的
include(ROOT_PATH . '/eccore/ecmall.php');

/* 定义配置信息 */
$ecm_cfg = include(ROOT_PATH . '/data/config.inc.php');
foreach ($ecm_cfg as $k => $v)
{
    if (strpos($k, 'UC_') !== false)
    {
        unset($ecm_cfg[$k]); // 不定义UC相关常量
    }
}
ecm_define($ecm_cfg);

/* 启动ECMall */
ECMall::startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  APP_ROOT . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/libraries/time.lib.php',
        APP_ROOT . '/app/integrater.base.php',
    ),
));

?>