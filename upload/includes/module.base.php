<?php

/* 接口BaseModule */
!defined('ROOT_PATH') && exit('Forbidden');

if (defined('IN_BACKEND'))
{
    /**
     *    模块后台控制器基础类
     *
     *    @author    Garbin
     *    @usage    none
     */
    class AdminbaseModule extends BaseModule
    {
        function __construct()
        {
            $this->AdminbaseModule();
        }
        function AdminbaseModule()
        {
            define_module();
            Lang::load(module_lang('common'));
            $this->visitor =& env('visitor');
            parent::__construct();
        }
        function _config_view()
        {
            parent::_config_view();
            $this->_view->template_dir  = MODULE_ABSPATH . '/templates/admin';
            $this->_view->res_base     = MODULE_WEBPATH . '/templates';
        }
    }
}
else
{
    /**
     *    模块前台控制器基础类
     *
     *    @author    Garbin
     *    @usage    none
     */
    class IndexbaseModule extends BaseModule
    {
        function __construct()
        {
            $this->IndexbaseModule();
        }
        function IndexbaseModule()
        {
            define_module();
            Lang::load(module_lang('common'));
            $this->visitor =& env('visitor');
            parent::__construct();
        }
        function _config_view()
        {
            parent::_config_view();
            $this->_view->template_dir = MODULE_ABSPATH . '/templates';
            $this->_view->res_base     = MODULE_WEBPATH . '/templates';
        }
    }
}

/**
 *    定义模型路径常量
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function define_module()
{
    /* 相对路径 */
    define('MODULE_RELPATH', 'external/modules/' . MODULE);

    /* 绝对路径 */
    define('MODULE_ABSPATH', ROOT_PATH . '/' . MODULE_RELPATH);

    /* URI */
    define('MODULE_WEBPATH', SITE_URL . '/' . MODULE_RELPATH);
}

/* 获取模块语言包路径 */
function module_lang($lang_pack)
{
    return ROOT_PATH . '/external/modules/' . MODULE . '/languages/' . LANG . '/' . $lang_pack . '.lang.php';
}

?>
