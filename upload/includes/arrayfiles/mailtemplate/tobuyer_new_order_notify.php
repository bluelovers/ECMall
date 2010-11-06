<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:您的訂單已生成',
  'content' => '<p>尊敬的{$order.buyer_name}:</p>
<p style="padding-left: 30px;">您在{$site_name}上下的訂單已生成，訂單號{$order.order_sn}。</p>
<p style="padding-left: 30px;">查看訂單詳細信息請點擊以下鏈接</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}">{$site_url}/index.php?app=buyer_order&amp;act=view&amp;order_id={$order.order_id}</a></p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>