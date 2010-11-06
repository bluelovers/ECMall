<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:店鋪{$order.seller_name}調整了您的訂單費用',
  'content' => '<p>尊敬的{$order.buyer_name}:</p>
<p style="padding-left: 30px;">與您交易的店鋪{$order.seller_name}調整了您訂單號為{$order.order_sn}的訂單的費用，請您及時付款。</p>
<p style="padding-left: 30px;">查看訂單詳細信息請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>