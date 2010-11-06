<?php

class BaseUpgraderApp extends BaseApp
{
    /* 升级步骤，默认只有一步 */
    var $_steps = array(
        'first_step',
    );

    /* 当前系统信息 */
    var $curr_system_info = array();

    /* 源版本 */
    var $source_version = null;

    function __construct()
    {
        $this->BaseUpgraderApp();
    }

    function BaseUpgraderApp()
    {
        /* 初始化环境数据 */
        parent::__construct();
        Lang::load(version_data('framework.lang.php'));
        Lang::load(version_data('common.lang.php'));

        $this->curr_system_info = include(ROOT_PATH . '/data/system.info.php');

        /* 检查是否符合升级条件 */
        $upgradable = $this->_check_upgradable();
        if (!$upgradable)
        {
            $error = current($this->get_error());
            $this->_exit($error['msg']);
        }

        /* 初始化升级信息 */
        !isset($_SESSION['step']) && $_SESSION['step'] = $this->_steps[0];
        $this->db =& db();
    }

    /**
     *    升级界面
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        if (!UPGRADING)
        {
            $_SESSION = array();
            $this->assign('steps', $this->_steps);
            $this->assign('lang', Lang::get());
            $this->assign('site_url', SITE_URL);
            $this->assign('charset', CHARSET);
            $this->assign('target_version', VERSION);
            $this->display('index.html');
        }
        else
        {
            $this->upgrading();
        }
    }

    /**
     *    升级处理
     *
     *    @author    Garbin
     *    @return    void
     */
    function upgrading()
    {
        /* 获取当前步骤 */
        $step = $this->_get_current_step();

        /* 处理步骤 */
        $step_result = $this->$step();
        if ($step_result === true)
        {
            $this->_step_done($step);
        }
        elseif ($step_result === false)
        {
            $this->_upgrade_failed($step);
        }
        else
        {
            $this->_step_continue($step_result);
        }
    }

    /**
     *    第一个步骤
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function first_step()
    {
        # 步骤完成
        # return true;

        # 分批处理

        #$batch = $this->_get_batch();
        #if ($batch != 10)
        #{
        #    sleep(1);
        #    return $this->_n($batch);
        #}
        #else
        #{
        #    sleep(1);
        #    return true;
        #}


        # 步骤失败
        # $this->_error('Mysql Error!');
        # return false;

        /* 默认只有简单的数据库调整 */
        $result = $this->_dbchange();
        if (!$result)
        {
            return false;
        }

        return true;
    }

    /**
     *    升级成功
     *
     *    @author    Garbin
     *    @return    void
     */
    function upgrade_done()
    {
        $done_msg = $this->on_done();
        $this->_update_system_info();
        $this->_respond("parent.upgrade_done('{$done_msg}');");
    }

    function on_done()
    {
        return Lang::get('upgrade_done');
    }

    /**
     *    默认的数据库更改
     *
     *    @author    Garbin
     *    @return    void
     */
    function _dbchange()
    {
        $common_db_change   = APP_ROOT . '/data/dbchange.sql';
        $version_db_change  = version_data('dbchange.sql');
        if (is_file($common_db_change))
        {
            if(!$this->_run_sql($common_db_change))
            {
                return false;
            }
        }
        if (is_file($version_db_change))
        {
            if (!$this->_run_sql($version_db_change))
            {
                return false;
            }
        }

        return true;
    }

    /**
     *    更新系统信息
     *
     *    @author    Garbin
     *    @return    void
     */
    function _update_system_info()
    {
        save_system_info(array(
            'version'   => VERSION,
            'release'   => RELEASE,
        ));
    }
    function _run_sql($sqls)
    {
        if (is_string($sqls))
        {
            $sqls = get_sql($sqls);
        }
        if (!is_array($sqls))
        {
            return true;
        }

        foreach ($sqls as $sql)
        {
            //$sql = replace_prefix('ecm_', DB_PREFIX, $sql);
            (substr($sql, 0, 12) == 'CREATE TABLE') && $sql = create_table($sql);

            $query = $this->db->query($sql, 'SILENT');
            if (!$query)
            {
                $this->_error($this->db->error());
                return false;
            }
        }

        return true;
    }

    /**
     *    获取当前运行步骤
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_current_step()
    {
        return $_SESSION['step'];
    }

    /**
     *    步骤完成
     *
     *    @author    Garbin
     *    @param     string $step
     *    @return    void
     */
    function _step_done($step)
    {
        $this->_change_step_status($step, 'done');

        /* 获取下一步 */
        $next_step = $this->_get_next_step($step);
        if ($next_step)
        {
            /* 开始下一步 */
            $this->_start_step($next_step);
            $this->_continue();
        }
        else
        {
            /* 升级完成 */
            $this->upgrade_done();
        }
    }

    /**
     *    步骤失败
     *
     *    @author    Garbin
     *    @param     string $step
     *    @return    void
     */
    function _upgrade_failed($step)
    {
        $this->_change_step_status($step, 'failed');
        $error = current($this->get_error());

        $this->_step_failed($step, $error['msg']);
    }

    function _step_continue($step_info)
    {
        /* 保存会话 */
        $_SESSION['batch'] = $step_info[1];

        /* 更新消息 */
        $this->_running_message($step_info[0]);

        $this->_continue();
    }

    /**
     *    获取当前批次
     *
     *    @author    Garbin
     *    @return    int
     */
    function _get_batch()
    {
        $batch = isset($_SESSION['batch']) ? $_SESSION['batch'] : 0;

        return $batch + 1;
    }
    /**
     *    获取下一步
     *
     *    @author    Garbin
     *    @param     string $curr_step
     *    @return    string
     */
    function _get_next_step($curr_step)
    {
        $next_step_index = array_search($curr_step, $this->_steps) + 1;

        return isset($this->_steps[$next_step_index]) ? $this->_steps[$next_step_index] : false;
    }

    /**
     *    开始进入步骤
     *
     *    @author    Garbin
     *    @param     string $step
     *    @return    void
     */
    function _start_step($step)
    {
        $_SESSION['batch'] = 0;
        $_SESSION['step']  = $step;
        $this->_change_step_status($step, 'running');
        $this->_running_message(sprintf(Lang::get('start_step'), Lang::get($step)));
    }

    /**
     *    改变页面步骤状态
     *
     *    @author    Garbin
     *    @param     string $step
     *    @param     string $status
     *    @return    void
     */
    function _change_step_status($step, $status)
    {
        $this->_respond("parent.change_step_status('{$step}', '{$status}');");
    }

    /**
     *    继续
     *
     *    @author    Garbin
     *    @return    void
     */
    function _continue()
    {
        $this->_respond('window.location.reload();');
    }

    function _running_message($msg)
    {
        $this->_respond("parent.running_message('{$msg}');");
    }

    function _step_failed($step, $msg)
    {
        $this->_respond("parent.step_failed('{$step}', \"{$msg}\");");
    }

    function _show_message($msg)
    {
        $this->_respond("parent.show_message('{$msg}');");
    }

    /**
     *    输出响应
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _respond($script)
    {
        $this->_echo('<script type="text/javascript">' . $script . '</script>');
    }
    function _header()
    {
        static $header = false;
        if ($header === false)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $header = true;
        }
    }

    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = APP_ROOT . '/templates';
        $this->_view->res_base      = site_url() . '/templates';
        $this->_view->direct_output = true;
        $this->_view->lib_base      = SITE_URL . '/includes/libraries/javascript';
    }
    function _init_session()
    {
        session_start();
    }
    function _exit($msg)
    {
        $msg = Lang::get($msg);
        $this->_echo($msg);
        exit;
    }
    function _echo($msg)
    {
        $this->_header();
        echo $msg;
    }

    /**
     *    检查是否符合升级条件
     *
     *    @author    Garbin
     *    @return    bool
     */
    function _check_upgradable()
    {
        /* 检查版本是否匹配 */
        if (str_replace(' ', '', $this->curr_system_info['version']) != str_replace(' ', '', $this->source_version))
        {
            $this->_error(Lang::get('source_release_mismatch'));

            return false;
        }

        /* 检查相关文件是否可写 */
        if (!is_writable(ROOT_PATH . '/data') || !is_writable(ROOT_PATH . '/temp') || !is_writable(ROOT_PATH . '/external/widgets'))
        {
            $this->_error(Lang::get('dir_unwritable'));

            return false;
        }

        return true;
    }

    /**
     *    处理完成批次信息
     *
     *    @author    Garbin
     *    @param     string $batch
     *    @return    string
     */
    function _n($batch)
    {
        return array(sprintf(Lang::get('batch_completed'), $batch), $batch);
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
 *    创建数据库表
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function create_table($sql) {
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
    (mysql_get_server_info() > '4.1' ? " ENGINE={$type} DEFAULT CHARSET=" . str_replace('-', '', CHARSET) : " TYPE={$type}");
}

/**
 *    从文件中获取SQL语句
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function get_sql($file)
{
    $contents = file_get_contents($file);
    $contents = str_replace("\r\n", "\n", $contents);
    $contents = trim(str_replace("\r", "\n", $contents));
    $return_items = $items = array();
    $items = explode(";\n", $contents);
    foreach ($items as $item)
    {
        $return_item = '';
        $item = trim($item);
        $lines = explode("\n", $item);
        foreach ($lines as $line)
        {
            if (isset($line[0]) && $line[0] == '#')
            {
                continue;
            }
            if (isset($line[1]) && $line[0] .  $line[1] == '--')
            {
                continue;
            }

            $return_item .= $line;
        }
        if ($return_item)
        {
            $return_items[] = $return_item;
        }
    }

    return $return_items;
}

/**
 *    创建新的配置文件
 *
 *    @author    Garbin
 *    @param     array $data
 *    @return    void
 */
function save_config_file($data)
{
    $contents = file_get_contents(version_data('config.sample.php'));
    file_put_contents(ROOT_PATH . '/data/config.inc.php', str_replace('{%CONFIG_ARRAY%}', var_export($data, true), $contents));
}

/**
 *    创建系统信息文件
 *
 *    @author    Garbin
 *    @param     array $info
 *    @return    void
 */
function save_system_info($info)
{
    return save_array(ROOT_PATH . '/data/system.info.php', $info);
}

/**
 *    保存数组到文件
 *
 *    @author    Garbin
 *    @param     string $file_path
 *    @param     array  $data
 *    @return    void
 */
function save_array($file_path, $array, $comment = '')
{
    if ($comment)
    {
        $comment .= '\r\n';
    }
    file_put_contents($file_path, "<?php\r\n{$comment}return " . var_export($array, true) . "; \r\n?>");
}

/**
 *    移动文件或目录到新位置，若目标目录不存在则创建
 *
 *    @author    Garbin
 *    @param     string $src
 *    @param     string $target
 *    @return    bool
 */
function move_file($src, $target)
{
    if (!file_exists($src))
    {
        return false;
    }
    if (is_file($src))
    {
        /* 如果是文件，移动 */
        $dirname = dirname($target);
        if (!file_exists($dirname))
        {
            /* 目录不存在，尝试创建 */
            if(!@mkpath($dirname))
            {
                return false;
            }
        }

        return @rename($src, $target);
    }
}

/**
 *  创建一条路径(递归地创建文件夹)
 *  @param  string $path
 *  @param  int    $mode
 *  @return bool
 */
function mkpath($path, $mode = 0777)
{
    if (is_dir($path))
    {
        return true;
    }
    if (@mkdir($path, $mode))
    {
        @touch($path . '/index.html');

        return true;
    }

    /* 递归的创建父亲文件夹 */
    if (!mkpath(dirname($path), $mode))
    {
        return false;
    }

    /* 创建自身 */
    $result = @mkdir($path, $mode);
    if ($result)
    {
        @touch($path . '/index.html');
    }

    return $result;
}

/**
 *    修改表前缀
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function replace_prefix($orig, $target, $sql)
{
    return str_replace('`' . $orig, '`' . $target, $sql);
}

?>
