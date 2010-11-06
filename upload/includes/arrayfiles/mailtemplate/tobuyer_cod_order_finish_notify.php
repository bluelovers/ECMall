<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:店鋪{$order.seller_name}確認收到了您的貨款，交易完成！',
  'content' => '<p>尊敬的{$order.buyer_name}:</p>
<p style="padding-left: 30px;">與您交易的店鋪{$order.seller_name}已經確認收到了您的貨到付款訂單{$order.order_sn}的付款，交易完成！您可以到用戶中心-&gt;我的訂單中對該交易進行評價。</p>
<p style="padding-left: 30px;">查看訂單詳細信息請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="padding-left: 30px;">查看我的訂單列表請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=buyer_order">{$site_url}/index.php?app=buyer_order</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>