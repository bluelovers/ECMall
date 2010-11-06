<?php

define('CHARSET', substr(LANG, 3));

class DefaultApp extends BaseApp
{
    /**
     *    选择需要整合的应用
     *
     *    @author    Garbin
     *    @return    array
     */
    function index()
    {
        Lang::load(version_data('common.lang.php'));
        $this->display('index.html');
    }

        /**
     *    模板引擎配置
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = APP_ROOT . '/templates';
        $this->_view->res_base      = site_url() . '/templates';
        $this->_view->direct_output = true;
        $this->_view->lib_base      = dirname(site_url()) . '/includes/libraries/javascript';
    }

    function display($tpl)
    {
        header('Content-type: text/html;charset=' . CHARSET);
        $this->assign('lang', Lang::get());
        $this->assign('charset', CHARSET);
        parent::display($tpl);
    }
}

/**
 *    版本数据
 *
 *    @author    Garbin
 *    @param     string $file
 *    @return    string
 */
function version_data($file)
{
    return APP_ROOT . '/versions/' . LANG . '/' . $file;
}

?>
