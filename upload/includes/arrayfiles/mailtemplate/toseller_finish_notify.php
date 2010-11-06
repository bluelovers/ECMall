<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:買家確認了與您交易的訂單{$order.order_sn}，交易完成',
  'content' => '<p>尊敬的{$order.seller_name}:</p>
<p style="padding-left: 30px;">買家{$order.buyer_name}已經確認了與您交易的訂單{$order.order_sn}。交易完成</p>
<p style="padding-left: 30px;">查看訂單詳細信息請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=seller_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=seller_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="padding-left: 30px;">查看您的訂單列表管理頁請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=seller_order">{$site_url}/index.php?app=seller_order</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>