<?php

class DefaultApp extends IntegraterApp
{
    function __construct()
    {
        $this->DefaultApp();
    }

    function DefaultApp()
    {
        parent::__construct();

        if (!file_exists(ROOT_PATH . '/uc_client/client.php'))
        {
            $this->show_message('uc_client_not_exist');
            return;
        }

        $this->db =& db();
    }

    /**
     *    获取流程地图
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_map()
    {
        return array(
            'config',     //用户配置
            'finish',     //整合完成
        );
    }

    /**
     *    配置表单，该步骤提交一个配置数组
     *
     *    @author    Garbin
     *    @return    void
     */
    function config()
    {
        $info = $this->db->getRow("SELECT user_name,email FROM `ecm_member` WHERE user_id=1");
        $this->assign('data', array(
            'uc_connect'  => isset($_POST['uc_connect']) ? $_POST['uc_connect'] : 'mysql',
            'uc_api'      => isset($_POST['uc_api']) ? trim($_POST['uc_api']) : 'http://',
            'uc_ip'       => isset($_POST['uc_ip']) ? trim($_POST['uc_ip']) : '',
            'uc_password' => isset($_POST['uc_password']) ? trim($_POST['uc_password']) : '',
            'app_name'    => isset($_POST['app_name']) ? trim($_POST['app_name']) : '',
            'app_url'     => isset($_POST['app_url']) ? trim($_POST['app_url']) : dirname(dirname(site_url())),
            'user_name'   => $info['user_name'],
            'email'       => $info['email'],
        ));
        $this->display('config.html');
    }

    /**
     *    配置表单的处理脚本
     *
     *    @author    Garbin
     *    @return    void
     */
    function config_done()
    {
        $uc_info = array(
            'uc_connect'  => $_POST['uc_connect'],
            'uc_api'      => trim($_POST['uc_api']),
            'uc_ip'       => isset($_POST['uc_ip']) ? trim($_POST['uc_ip']) : '',
            'uc_password' => trim($_POST['uc_password']),
            'app_name'    => trim($_POST['app_name']),
            'app_url'     => trim($_POST['app_url']),
        );

        $_recheck   = empty($_POST['recheck']) ? 0 : intval($_POST['recheck']);
        $_admin_user = empty($_POST['admin_user']) ? '' : trim($_POST['admin_user']);
        $_user_name = empty($_POST['user_name']) ? '' : trim($_POST['user_name']);
        $_password = empty($_POST['password']) ? '' : trim($_POST['password']);
        $_password_confirm = empty($_POST['password_confirm']) ? '' : trim($_POST['password_confirm']);
        $_email = empty($_POST['email']) ? '' : trim($_POST['email']);
        $_accept = empty($_POST['accept']) ? false : true;

        if (!$_accept)
        {
            $this->_ierror(array('error' => array(
                'key'     => 'accept_warning',
                'content' => Lang::get('please_read_warning'),
            )));
            return false;
        }
        $missing_items = array();
        if (strtolower($uc_info['uc_api']) == 'http://')
        {
            $missing_items[] = 'uc_api';
        }

        $_except_array = array('uc_connect', 'uc_ip', 'recheck', 'uc_conf');
        if ($_admin_user == 'exists_user')
        {
            $_except_array[] = 'password_confirm';
            $_except_array[] = 'email';
        }
        foreach ($_POST as $key => $value)
        {
            if (empty($value) && !in_array($key, $_except_array))
            {
                $missing_items[] = $key;
            }
        }

        if (!empty($missing_items))
        {
            $this->_ierror(array('missing_items' => $missing_items));

            return false;
        }

        /* 检查注册新用户的信息 */
        if ($_admin_user == 'new_user')
        {
            if ($_password_confirm != $_password)
            {
                $this->_ierror(array('error' => array(
                    'key'     => 'password_confirm',
                    'content' => Lang::get('password_inconsistent'),
                )));

                return false;
            }

            if (!is_email($_email))
            {
                $this->_ierror(array(
                    'error' => array(
                        'key'     => 'email',
                        'content' => Lang::get('email_error'),
                )));

                return false;
            }
        }

        if (!preg_match("/^http(s?):\/\//i", $uc_info['uc_api']))
        {
            $this->_ierror(array(
                'error' => array(
                    'key'     => 'uc_api',
                    'content' => Lang::get('uc_api_error'),
            )));
            return false;
        }
        if (!preg_match("/^http(s?):\/\//i", $uc_info['app_url']))
        {
            $this->_ierror(array(
                'error' => array(
                    'key'     => 'app_url',
                    'content' => Lang::get('app_url_error'),
            )));

            return false;
        }

        /* 如果没有设置UCIP */
        if(empty($uc_info['uc_ip']))
        {
            $temp = @parse_url($uc_info['uc_api']);
            $uc_info['uc_ip'] = gethostbyname($temp['host']);
            if (ip2long($uc_info['uc_ip']) == -1 || ip2long($uc_info['uc_ip']) === FALSE)
            {
                $uc_info['uc_ip'] = '';
                $this->_ierror(array(
                    'error' => array(
                        'key'     => 'uc_api',
                        'content' => Lang::get('dns_error'),
                        'uc_ip'   => 1, // 显示ip
                )));

                return false;
            }
        }

        $error = array();
        $ucversion = '';
        define('UC_API', $uc_info['uc_api']);
        define('UC_CONNECT', $uc_info['uc_connect']);
        require_once(ROOT_PATH . '/uc_client/client.php');
        
        if (!$_recheck)
        {
            @set_time_limit(0); // 抑制安全模式报错
            /* 第一次提交，得提交信息到UCenter */
            $tmp = @ecm_fopen($uc_info['uc_api'].'/index.php?m=app&a=ucinfo', 500, '', false, 1, $uc_info['uc_ip']);
            if (!empty($tmp))
            {
                $arr = explode('|', $tmp);
                if (count($arr) > 1)
                {
                    list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = $arr;
                    //if (!defined('UC_API'))
                    //{
                    //    define('UC_API', 1);
                    //}
                    //require_once(ROOT_PATH . '/uc_client/client.php');
                    //$uc_client_version = defined('UC_VERSION') ? UC_VERSION : UC_CLIENT_VERSION;

                    /* 检测是否连接成功 */
                    if ($status != 'UC_STATUS_OK')
                    {
                        $error = array('content' => Lang::get('get_ucinfo_failed'));
                    }
                    /* 检测字符集是否匹配 */
                    elseif (CHARSET != strtolower($uccharset))
                    {
                        $error = array('content' => sprintf(Lang::get('charset_error'), CHARSET, $uccharset));
                    }
                    /* 检测UC客户端版本 */
                    //elseif($uc_client_version != $ucversion)
                    //{
                    //    $error = array('content' => sprintf(Lang::get('version_error'), $ucversion, $uc_client_version));
                    //}
                    /* 检测UC客户端版本 */
                    elseif(strpos($apptypes, 'ECMALL') !== false)
                    {
                        $error = array('content' => Lang::get('app_exists'));
                    }
                }
                else
                {
                    $error = array('content' => Lang::get('connect_error'));
                }
            }
            else
            {
                $error = array('content' => Lang::get('get_ucinfo_failed'));
            }
            if ($error)
            {
                $this->_ierror(array(
                    'error' => $error
                ));

                return false;
            }

            /* 如果连接UC Server没有错误 */
            $app_type = 'ECMALL';

            /* tag 模板 */
            $app_tagtemplates = 'apptagtemplates[template]='.urlencode('<dl><dt>{goods_name}</dt><dd><a href="{url}"><img src="{image}"></a></dd><dd>{goods_price}</dd></dl>').'&'.
                                'apptagtemplates[fields][goods_name]='.urlencode(Lang::get('apptagtemplates.goods_name')).'&'.
                                'apptagtemplates[fields][uid]='.urlencode(Lang::get('apptagtemplates.uid')).'&'.
                                'apptagtemplates[fields][username]='.urlencode(Lang::get('apptagtemplates.username')).'&'.
                                'apptagtemplates[fields][dateline]='.urlencode(Lang::get('apptagtemplates.dateline')).'&'.
                                'apptagtemplates[fields][url]='.urlencode(Lang::get('apptagtemplates.url')) . '&'.
                                'apptagtemplates[fields][image]='.urlencode(Lang::get('apptagtemplates.image')) . '&'.
                                'apptagtemplates[fields][goods_price]='.urlencode(Lang::get('apptagtemplates.goods_price'));

            $postdata = 'm=app&a=add&ucfounder=&ucfounderpw='.urlencode($uc_info['uc_password']).
                        '&apptype='.urlencode($app_type).'&appname='.urlencode($uc_info['app_name']).
                        '&appurl='.urlencode($uc_info['app_url']).'&appip=&appcharset='.CHARSET.
                        '&appdbcharset='.CHARSET.'&'.$app_tagtemplates . '&release=' . UC_CLIENT_RELEASE;

            $uc_config = @ecm_fopen($uc_info['uc_api'].'/index.php', 500, $postdata, '', 1, $uc_info['uc_ip']);

            $arr = explode('|', $uc_config);

            if ($uc_config == -1)
            {
                $this->_ierror(array(
                    'error' => array(
                        'content' => Lang::get('password_error'),
                )));

                return false;
            }

            $conf['MEMBER_TYPE']   = 'uc';
            $conf['UC_DBCHARSET']  = $arr[6];
            $conf['UC_DBTABLEPRE'] = '`'. $arr[3] .'`.' . $arr[7];
            $conf['UC_KEY']        = $arr[0];
            $conf['UC_APPID']      = $arr[1];
            $conf['UC_DBHOST']     = $arr[2];
            $conf['UC_DBNAME']     = $arr[3];
            $conf['UC_DBUSER']     = $arr[4];
            $conf['UC_DBPW']       = $arr[5];
            $conf['UC_CHARSET']    = $arr[8];

            $conf['UC_API']        = $uc_info['uc_api'];
            $conf['UC_PATH']       = 'uc_client';
            $conf['UC_CONNECT']    = $uc_info['uc_connect'];
            $conf['UC_IP']         = $uc_info['uc_ip'];
            $conf['UC_DBCONNECT']  = '0';
        }
        else
        {
            $conf = $_POST['uc_conf'];
        }

        /* 验证UC数据库是否连接成功 */
        if ($conf['UC_CONNECT'] == 'mysql')
        {
            $link = @mysql_connect($conf['UC_DBHOST'], $conf['UC_DBUSER'], $conf['UC_DBPW'], 1);
            $res = $link && mysql_select_db($conf['UC_DBNAME'], $link) ? true : false;
        }

        if ($conf['UC_CONNECT'] == '' || ($conf['UC_CONNECT'] == 'mysql' && $res))
        {
            /* 连接UC */
            $define_arr = $conf;
            unset($define_arr['MEMBER_TYPE']);
            unset($define_arr['UC_API']);
            unset($define_arr['UC_CONNECT']);
            ecm_define($define_arr);

            /* 检查UC Client版本 */
            $uc_client_version = defined('UC_VERSION') ? UC_VERSION : UC_CLIENT_VERSION;
            if ($uc_client_version != $ucversion && !$_recheck)
            {
                $error = array('content' => sprintf(Lang::get('version_error'), $ucversion, $uc_client_version));
                $this->_ierror(array(
                    'error' => $error
                ));

                return false;
            }

            /* 创建管理员 */
            switch ($_admin_user)
            {
                case 'exists_user':
                    /* 已存在的用户 */

                    /* 到UC验证一下用户是否正确 */
                    $user_info = uc_user_login($_user_name, $_password);
                    if ($user_info[0] < 0)
                    {
                        /* 不存在，重新选择 */
                        switch ($user_info[0])
                        {
                            case -1:    //用户不存在
                                $error = array(
                                    'key'       => 'user_name',
                                    'content'   => Lang::get('user_not_exists')
                                );
                            break;
                            case -2:    //密码错误
                                $error = array(
                                    'key'       => 'password',
                                    'content'   => Lang::get('admin_password_error')
                                );
                            break;
                            default:
                                $error = array(
                                    'content'   => Lang::get('unknow_error')
                                );
                            break;
                        }
                        $this->_ierror(array(
                            'error' => $error,
                            'recheck'   => 1,
                            'uc_conf'   => $conf,
                        ));

                        return false;
                    }

                    /* 管理员信息 */
                    $admin = array(
                        'user_id'   => $user_info[0],
                        'user_name'   => $user_info[1],
                        'email'   => $user_info[3],
                    );
                break;
                case 'new_user':
                    $user_id = uc_user_register($_user_name, $_password, $_email);
                    if ($user_id < 0)
                    {
                        switch ($user_id)
                        {
                            case -1:    //用户名不合法
                                $error = array(
                                    'key'       => 'user_name',
                                    'content'   => Lang::get('invalid_user_name')
                                );
                            break;
                            case -2:    //包含不允许注册的词语
                                $error = array(
                                    'key'       => 'user_name',
                                    'content'   => Lang::get('blocked_usre_name')
                                );
                            break;
                            case -3:    //用户已存在
                                $error = array(
                                    'key'       => 'user_name',
                                    'content'   => Lang::get('user_exists')
                                );
                            break;
                            case -4:    //EMail格式有误
                                $error = array(
                                    'key'       => 'email',
                                    'content'   => Lang::get('email_error')
                                );
                            break;
                            case -5:    //EMail不允许注册
                                $error = array(
                                    'key'       => 'email',
                                    'content'   => Lang::get('blocked_email')
                                );
                            break;
                            case -6:    //EMail已被注册
                                $error = array(
                                    'key'       => 'email',
                                    'content'   => Lang::get('email_exists')
                                );
                            break;
                            default:
                                $error = array(
                                    'content'   => Lang::get('unknow_error')
                                );
                            break;
                        }
                        $this->_ierror(array(
                            'error' => $error,
                            'recheck'   => 1,
                            'uc_conf'   => $conf,
                        ));

                        return false;
                    }

                    /* 管理员信息 */
                    $admin = array(
                        'user_id'   => $user_id,
                        'user_name'   => $_user_name,
                        'email'   => $_email,
                    );
                break;
            }

            /* 初始化管理员 */
            $reg_time = gmtime();
            $this->db->query("delete from `ecm_address`");
            $this->db->query("delete from `ecm_article` where store_id > 0");
            $this->db->query("delete from `ecm_cart`");
            $this->db->query("delete from `ecm_category_goods`");
            $this->db->query("delete from `ecm_category_store`");
            $this->db->query("delete from `ecm_collect`");
            $this->db->query("delete from `ecm_friend`");
            $this->db->query("delete from `ecm_gcategory` where store_id > 0");
            $this->db->query("delete from `ecm_goods`");
            $this->db->query("delete from `ecm_goods_image`");
            $this->db->query("delete from `ecm_goods_qa`");
            $this->db->query("delete from `ecm_goods_spec`");
            $this->db->query("delete from `ecm_goods_statistics`");
            $this->db->query("delete from `ecm_mail_queue`");
            $this->db->query("delete from `ecm_member`");
            $this->db->query("delete from `ecm_message`");
            $this->db->query("delete from `ecm_order`");
            $this->db->query("delete from `ecm_order_extm`");
            $this->db->query("delete from `ecm_order_goods`");
            $this->db->query("delete from `ecm_order_log`");
            $this->db->query("delete from `ecm_partner` where store_id > 0");
            $this->db->query("delete from `ecm_payment`");
            $this->db->query("delete from `ecm_recommend` where store_id > 0");
            $this->db->query("delete from `ecm_recommended_goods`");
            $this->db->query("delete from `ecm_shipping`");
            $this->db->query("delete from `ecm_store`");
            $this->db->query("delete from `ecm_uploaded_file` where store_id > 0");
            $this->db->query("delete from `ecm_user_priv`");
            $this->db->query("INSERT INTO `ecm_member`(user_id, user_name, email, reg_time) VALUES({$admin['user_id']}, '{$admin['user_name']}', '{$admin['email']}', {$reg_time})");
            $this->db->query("INSERT INTO `ecm_user_priv`(user_id, store_id, privs) VALUES({$admin['user_id']}, 0, 'all')");

            /* 生成 UC 配置文件 */
            $config_file = ROOT_PATH . '/data/config.inc.php';
            $config = include ($config_file);
            foreach ($conf as $key => $value)
            {
                $config[$key] = $conf[$key];
            }
            file_put_contents($config_file, "<?php\r\nreturn " . var_export($config, true) . ";\r\n?>");
        }
        else
        {
            $this->_ierror(array(
                'error' => array(
                    'content' => Lang::get('uc_db_error'),
            )));

            return false;
        }

        /* 锁定整合程序 */
        touch(LOCK_FILE);
        return true;
    }

    function finish()
    {
        $this->assign('site_url', dirname(dirname(site_url())));
        $this->display('finish.html');
    }
    function _ierror($error)
    {
        $this->_doing = $this->_done;
        if (!isset($error['recheck']))
        {
            $error['recheck'] = intval($_POST['recheck']);
        }
        if (!isset($error['uc_conf']))
        {
            $error['uc_conf'] = empty($_POST['uc_conf']) ? array() : $_POST['uc_conf'];
        }
        $error['admin_user'] = trim($_POST['admin_user']);
        $this->assign($error);
        $this->config();
    }
}

?>
