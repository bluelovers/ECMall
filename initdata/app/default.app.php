<?php

/* 安裝測試數據 */

define('CHARSET', substr(LANG, 3));
define('LOCK_FILE', ROOT_PATH . '/data/initdata.lock');

class DefaultApp extends BaseApp
{
    var $_seller_id = 0;
    var $_buyer_id  = 0;

    function __construct()
    {
        $this->DefaultApp();
    }

    function DefaultApp()
    {
        if (file_exists(LOCK_FILE))
        {
            $this->show_message('您好，本程序已鎖定。如果您想再次運行本程序，請先刪除 data/initdata.lock 文件。');
            return;
        }
        parent::__construct();
    }

    function index()
    {
        if (!IS_POST)
        {
            $this->display('index.html');
        }
        else
        {
            $seller_name = empty($_POST['seller']) ? '' : trim($_POST['seller']);
            $buyer_name  = empty($_POST['buyer']) ? '' : trim($_POST['buyer']);
            if (!$seller_name || !$buyer_name)
            {
                $this->show_message('請填寫賣家用戶名和買家用戶名');
                return;
            }

            /* 檢查用戶名 */
            $ms =& ms();
            if (!$ms->user->check_username($seller_name))
            {
                $error_msg = array(
                    'user_exists' => '賣家用戶名已存在，請您換一個',
                    'invalid_user_name' => '賣家用戶名不符合要求，請您換一個',
                    'blocked_user_name' => '賣家用戶名不符合要求，請您換一個',
                    'unknow_error' => '賣家用戶名不符合要求，請您換一個',
                );
                $error = $ms->user->get_error();
                $this->show_message($error_msg[$error[0]['msg']]);
                return;
            }
            if (!$ms->user->check_username($buyer_name))
            {
                $error_msg = array(
                    'user_exists' => '買家用戶名已存在，請您換一個',
                    'invalid_user_name' => '買家用戶名不符合要求，請您換一個',
                    'blocked_user_name' => '買家用戶名不符合要求，請您換一個',
                    'unknow_error' => '買家用戶名不符合要求，請您換一個',
                );
                $error = $ms->user->get_error();
                $this->show_message($error_msg[$error[0]['msg']]);
                return;
            }

            /* 注冊用戶 */
            $this->_seller_id = $ms->user->register($seller_name, '123456', 'seller@ecmall.com', array('real_name' => '超級賣家'));
            $this->_buyer_id  = $ms->user->register($buyer_name, '123456', 'buyer@ecmall.com', array('real_name' => '超級買家'));

            /* 復制文件 */
            copy_files(APP_ROOT . '/data', ROOT_PATH . '/data');

            /* 運行sql */
            $mod =& m('privilege');
            $sqls = get_sql(APP_ROOT . '/initdata.sql');
            foreach ($sqls as $sql)
            {
                $sql = str_replace('{seller_id}', $this->_seller_id, $sql);
                $sql = str_replace('{buyer_id}', $this->_buyer_id, $sql);
                $mod->db->query($sql);
            }

            /* 清除緩存 */
            $cache_server =& cache_server();
            $cache_server->clear();

            /* 鎖定文件 */
            touch(LOCK_FILE);

            /* 運行成功 */
            $this->show_message('恭喜！測試數據安裝成功！');
        }
    }

    function display($f)
    {
        $this->assign('charset', CHARSET);

        parent::display($f);
    }

    function show_message($msg)
    {
        header('Content-Type:text/html;charset=' . CHARSET);
        dump($msg);
    }

    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = APP_ROOT . '/templates';
        $this->_view->res_base      = site_url() . '/templates';
        $this->_view->direct_output = true;
        $this->_view->lib_base      = dirname(site_url()) . '/includes/libraries/javascript';
    }
}

function copy_files($source, $target)
{
    if (is_dir($source))
    {
        if (!file_exists($target))
        {
            ecm_mkdir($target);
        }

        $dh = opendir($source);
        while (($file = readdir($dh)) !== false)
        {
            if ($file{0} != '.')
            {
                copy_files($source . '/' . $file, $target . '/' . $file);
            }
        }
        closedir($dh);
    }
    else
    {
        copy($source, $target);
        @chmod($target, 0777);
    }
}

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

?>