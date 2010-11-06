<?php

/**
 * 店鋪地址簡寫插件
 *
 * @return  array
 */
class Short_store_urlPlugin extends BasePlugin
{
    function execute()
    {
        if (defined('IN_BACKEND') && IN_BACKEND === true)
        {
            return; // 後台無需執行
        }
        elseif($store_id = intval(current(array_keys($_GET))))
        {
            header('location:index.php?app=store&id=' . $store_id);
        }
    }
}

?>