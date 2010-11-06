<?php 
return array (
  'version' => '1.0',
  'subject' => '您獲得了來自{$coupon.store_name}的優惠券',
  'content' => '<p>尊敬的{$user.user_name}，</p>
<p>&nbsp;&nbsp;&nbsp; 您好，恭喜您獲得了一個來自{$coupon.store_name}店鋪的優惠券。</p>
<p>&nbsp;&nbsp;&nbsp; 優惠金額︰{$coupon.coupon_value|price}</p>
<p>&nbsp;&nbsp;&nbsp; 有效期︰{$coupon.start_time|date}至{$coupon.end_time|date}</p>
<p>&nbsp;&nbsp;&nbsp; 優惠券號碼︰{$user.coupon.coupon_sn}</p>
<p>&nbsp;&nbsp;&nbsp; 使用條件︰購物滿{$coupon.min_amount|price}即可使用</p>
<p>&nbsp;&nbsp;&nbsp; 店鋪地址︰<a href="{$site_url}/index.php?app=store&amp;id={$coupon.store_id}">{$coupon.store_name}</a></p>
<p style="padding-left: 30px;">&nbsp;</p>
<p style="text-align: right;">網站名稱︰{$site_name}</p>
<p style="text-align: right;">日期︰{$mail_send_time}</p>',
);
?>