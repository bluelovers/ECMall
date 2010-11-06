<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    支付方式基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePayment extends Object
{
    /* 外部处理网关 */
    var $_gateway   = '';
    /* 支付方式唯一标识 */
    var $_code      = '';


    function __construct($payment_info = array())
    {
        $this->BasePayment($payment_info);
    }
    function BasePayment($payment_info = array())
    {
        $this->_info   = $payment_info;
        $this->_config = unserialize($payment_info['config']);
    }

    /**
     *    获取支付表单
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    array
     */
    function get_payform()
    {
        return $this->_create_payform('POST');
    }

    /**
     *    获取规范的支付表单数据
     *
     *    @author    Garbin
     *    @param     string $method
     *    @param     array  $params
     *    @return    void
     */
    function _create_payform($method = '', $params = array())
    {
        return array(
            'online'    =>  $this->_info['is_online'],
            'desc'      =>  $this->_info['payment_desc'],
            'method'    =>  $method,
            'gateway'   =>  $this->_gateway,
            'params'    =>  $params,
        );
    }

    /**
     *    获取通知地址
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_notify_url($order_id)
    {
        return SITE_URL . "/index.php?app=paynotify&act=notify&order_id={$order_id}";
    }

    /**
     *    获取返回地址
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_return_url($order_id)
    {
        return SITE_URL . "/index.php?app=paynotify&order_id={$order_id}";
    }

    /**
     *    获取外部交易号
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* 将此数据写入订单中 */
            $model_order =& m('order');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }

    /**
     *    获取商品简介
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_subject($order_info)
    {
        return 'ECMall Order:' . $order_info['order_sn'];
    }

    /**
     *    获取通知信息
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_notify()
    {
        /* 如果有POST的数据，则认为POST的数据是通知内容 */
        if (!empty($_POST))
        {
            return $_POST;
        }

        /* 否则就认为是GET的 */
        return $_GET;
    }

    /**
     *    验证支付结果
     *
     *    @author    Garbin
     *    @return    void
     */
    function verify_notify()
    {
        #TODO
    }

    /**
     *    将验证结果反馈给网关
     *
     *    @author    Garbin
     *    @param     bool   $result
     *    @return    void
     */
    function verify_result($result)
    {
        if ($result)
        {
            echo 'success';
        }
        else
        {
            echo 'fail';
        }
    }
}

?>