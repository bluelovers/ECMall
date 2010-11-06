<?php
return array (
  'version' => '1.0',
  'subject' => '{$site_name}提醒:{$user.user_name}修改密碼設置',
  'content' => '<p>尊敬的{$user.user_name}:</p>
<p style="padding-left: 30px;">您好, 您剛才在 {$site_name} 申請了重置密碼，請點擊下面的鏈接進行重置︰</p>
<p style="padding-left: 30px;"><a href="{$site_url}/index.php?app=find_password&act=set_password&id={$user.user_id}&activation={$word}">{$site_url}/index.php?app=find_password&act=set_password&id={$user.user_id}&activation={$word}</a></p>
<p style="padding-left: 30px;">此鏈接只能使用一次, 如果失效請重新申請. 如果以上鏈接無法點擊，請將它拷貝到瀏覽器(例如IE)的地址欄中。</p>
<p style="text-align: right;">{$site_name}</p>
<p style="text-align: right;">{$mail_send_time}</p>',
);
?>