<?php

define('CHARSET', substr(LANG, 3));
define('LOCK_FILE', ROOT_PATH . '/data/integrate.lock');

/**
 *    安装程序基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class IntegraterApp extends BaseApp
{
    /* 当前完成的步骤 */
    var $_done = '';

    /* 当前正在做的步骤 */
    var $_doing= '';

    var $_hiddens = array();

    /**
     *    构造函数
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function __construct()
    {
        $this->IntegraterApp();
    }
    function IntegraterApp()
    {
        parent::__construct();

        Lang::load(version_data('common.lang.php'));

        if (file_exists(LOCK_FILE))
        {
            $this->show_message('integrate_locked');
            return;
        }
    }
    /**
     *    安装程序索引
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $done  = empty($_GET['done']) ? '' : trim($_GET['done']);
        $this->_done = $done;
        $doing = $this->_get_doing();
        $this->_doing = $doing;
        $ondone = $done . '_done';
        if (method_exists($this, $ondone))
        {
            /* 有结果检测 */
            if ($this->$ondone())
            {
                /* 检测通过，进行下一步 */
                $this->$doing();
            }
        }
        else
        {
            /* 无结果检测，直接进行下一步 */
            $this->$doing();
        }
    }

    /**
     *    安装完成提示
     *
     *    @author    Garbin
     *    @return    void
     */
    function _integrate_finished()
    {
        echo 'Integrate finished';
    }

    /**
     *    获取当前步骤
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_doing()
    {
        $map = $this->_get_map();
        if (!$this->_done)
        {
            return current($map);
        }

        $key = array_search($this->_done, $this->_get_map());
        $nkey = $key + 1;
        if (isset($map[$nkey]))
        {
            return $map[$nkey];
        }
        else
        {
            return '_integrate_finished';
        }
    }

    /**
     *    获取地图
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_map()
    {
        return array();
    }

    function _init_session(){}

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
        $this->_view->lib_base      = dirname(dirname(site_url())) . '/includes/libraries/javascript';
    }

    /**
     *    检查环境
     *
     *    @author    Garbin
     *    @param     array $required
     *    @return    array
     */
    function _check_env($required)
    {
        $return  = array('detail' => array(), 'compatible' => true, 'msg' => array());
        foreach ($required as $key => $value)
        {
            $checker = $value['checker'];
            $result = $checker();
            $return['detail'][$key] = array(
                'required'  => $value['required'],
                'current'   => $result['current'],
                'result'    => $result['result'] ? 'pass' : 'failed',
            );
            if (!$result['result'])
            {
                $return['compatible'] = false;
                $return['msg'][] = Lang::get($key . '_error');
            }
        }

        return $return;
    }

    /**
     *    检查文件是否可写
     *
     *    @author    Garbin
     *    @param     array $file_list
     *    @return    array
     */
    function _check_file($file_list)
    {
        $return = array('detail' => array(), 'compatible' => true, 'msg' => array());
        foreach ($file_list as $key => $value)
        {
            $result = check_file(ROOT_PATH . '/' . $value);
            $return['detail'][] = array(
                'file'  =>  $value,
                'result'=>  $result ? 'pass' : 'failed',
                'current'   =>  $result ? Lang::get('writable') : Lang::get('unwritable'),
            );
            if (!$result)
            {
                $return['compatible'] = false;
                $return['msg'][] = sprintf(Lang::get('file_error'), $value);
            }
        }

        return $return;
    }

    function display($tpl)
    {
        header('Content-type: text/html;charset=' . CHARSET);
        $map = $this->_get_map();
        $this->assign('lang', Lang::get());
        $this->assign('charset', CHARSET);
        $this->assign('done', $this->_done);
        $this->assign('doing', $this->_doing);
        $this->assign('step_num', array_search($this->_doing, $map) + 1);
        $this->assign('step_name', Lang::get("{$this->_doing}_title"));
        $this->assign('step_desc', Lang::get("{$this->_doing}_desc"));
        $this->assign('hiddens', $this->_hiddens);
        $this->assign('map', $map);
        parent::display($tpl);
    }

    function show_warning($msg)
    {
        header('Content-Type:text/html;charset=' . CHARSET);
        $title = Lang::get('warning');
        $msg = Lang::get($msg);
echo <<<TPL
        <html>
        <head>
        <title></title>
        </head>
        <body>
        <script type="text/javascript">alert("{$msg}");window.history.go(-1);</script>
        </body>
        </html>
TPL;
    }

    function show_message($msg)
    {
        header('Content-Type:text/html;charset=' . CHARSET);
        $msg = Lang::get($msg);
        dump($msg);
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

/**
 *    检查文件或目录是否可写
 *
 *    @author    Garbin
 *    @param     string $file
 *    @return    bool
 */
function check_file($file)
{
    if (!file_exists($file))
    {
        //不存在，则不可写
        return false;
    }
    #TODO 在Windows的服务器上可能会存在问题，待发现
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    {
        if (is_dir($file))
        {
            /* 如果是目录，则尝试创建文件并修改 */
            $trail = substr($file, -1);
            if ($trail == '/' || $trail == '\\')
            {
                $tmpfile = $file . '___test_dir_file.txt';
            }
            else
            {
                $tmpfile = $file . '/' . '___test_dir_file.txt';
            }
            /* 尝试创建文件 */
            if (false === @touch($tmpfile))
            {
                /* 不可写 */

                return false;
            }
            /* 创建文件成功 */
            /* 尝试修改该文件 */
            if (false === @touch($tmpfile))
            {
                return false;
            }

            /* 修改文件成功 */
            /* 删除文件 */
            @unlink($tmpfile);

            return true;
        }
        else
        {
            /* 如果是文件，则尝试修改文件 */
            if (false === @touch($file))
            {
                /* 修改不成功，不可写 */

                return false;
            }
            else
            {
                /* 修改成功，可写 */

                return true;
            }
        }
    }
    else
    {
        return is_writable($file);
    }
}

?>