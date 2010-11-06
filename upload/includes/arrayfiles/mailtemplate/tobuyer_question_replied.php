<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:{$user.user_name}您的{$type}咨詢已得到回復',
  'content' => '<p>尊敬的用戶:</p>
<p style="padding-left: 30px;">您好, 您在 {$site_name} 中的“{$item_name}”咨詢已得到回復，請點擊下面的鏈接查看︰</p>
<p style="padding-left: 30px;"><a href="{$url}">{$url}</a></p>
<p style="padding-left: 30px;"> 如果以上鏈接無法點擊，請將它拷貝到瀏覽器(例如IE)的地址欄中。</p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>