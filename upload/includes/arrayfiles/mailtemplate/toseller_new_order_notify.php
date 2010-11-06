<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:您有一個新訂單需要處理',
  'content' => '<p>尊敬的{$order.seller_name}:</p>
<p style="padding-left: 30px;">您有一個新的訂單需要處理，訂單號{$order.order_sn}，請盡快處理。</p>
<p style="padding-left: 30px;">查看訂單詳細信息請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=seller_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=seller_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="padding-left: 30px;">查看您的訂單列表管理頁請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=seller_order">{$site_url}/index.php?app=seller_order</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>