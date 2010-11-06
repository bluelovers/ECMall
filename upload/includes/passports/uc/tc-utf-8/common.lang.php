<?php
return array(
'toseller_store_closed_notify' => '您的店鋪已被關閉，原因是︰{$reason}',
    'toseller_store_opened_notify' => '您的店鋪已開通',
    'toseller_store_expired_closed_notify' => '您的店鋪已被關閉，原因是︰店鋪已到期',
    'toseller_groupbuy_end_notify' => '請盡快到“已結束的團購”完成該團購活動，以便買家可以完成交易，如結束後{$cancel_days}天未確認完成，該活動將被自動取消,查看[url={$site_url}/index.php?app=seller_groupbuy&state=end]已結束的團購[/url]',
    'tobuyer_groupbuy_cancel_notify' => '團購活動被賣家取消,原因如下︰' . "\r\n" . '{$reason}' . "\r\n" . '[url={$url}]查看詳情[/url]',
    'tobuyer_group_auto_cancel_notify' => '團購活動結束{$cancel_days}天後賣家未確認完成，活動自動取消，[url={$url}]查看詳情[/url]',
    'touser_send_coupon' => '您收到了 “{$store_name}” 發送來的優惠券' . "\r\n" . '優惠金額︰{$price}' . "\r\n" . '有效期︰{$start_time} 至{$end_time}' . "\r\n" . '優惠券號碼︰{$coupon_sn}' . "\r\n" . '使用條件︰購物滿 {$min_amount} 即可使用' . "\r\n" . '店鋪地址︰[url={$url}]{$store_name}[/url]',
    'tobuyer_groupbuy_finished_notify' => '“{$group_name}”活動成功完成，請盡快購買活動商品。[url={$site_url}/index.php?app=order&goods=groupbuy&group_id={$id}]點此購買[/url]',
    'toseller_goods_droped_notify' => '管理員刪除了您的商品︰{$goods_name}' . "\r\n" . '原因是︰{$reason}',
    'toseller_brand_passed_notify' => '恭喜！您申請的品牌 {$brand_name} 已通過審核。',
    'toseller_brand_refused_notify' => '抱歉，您申請的品牌 {$brand_name} 已被拒絕，原因如下︰' . "\r\n" . '{$reason}',
    'toseller_store_droped_notify' => '您的店鋪已被刪除',
    'toseller_store_passed_notify' => '恭喜，您的店鋪已開通，趕快來用戶中心發布商品吧。',
    'toseller_store_refused_notify'=> '抱歉，您的開店申請已被拒絕，原因如下︰ {$reason}',

    'code_example' => "字體加粗︰[b]加粗[/b]<br />圖片標簽︰[img]http://ecmall.shopex.cn/images/logo.gif[/img]<br/>超鏈接標簽︰[url=http://ecmall.shopex.cn]ECMall官方網站[/url]<br/>引用標簽︰[quote]ECMall[/quote]<br />代碼標簽︰[code]array('2222','3333')[/code]",
);
?>
