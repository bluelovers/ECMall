<?php

//---修改本文件請務必小心!並做好相應備份---
/*
配置說明:

SITE_URL        :   網站訪問地址, 當您發生訪問地址修改時修改, 必須帶http://, 不要在末尾添加'/'

DB_CONFIG       :   數據庫訪問配置(協議://用戶名:密碼@數據庫服務器地址:端口/數據庫名)

DB_PREFIX       :   數據庫表名前綴

LANG            :   字符集與語言

COOKIE_DOMAIN   :   網站Cookie作用域

COOKIE_PATH     :   網站Cookie作用路徑

ECM_KEY         :   網站密鑰

MALL_SITE_ID    :   網站ID, 不可修改

ENABLED_GZIP    :   GZIP開關,開啟GZIP後將提升用戶的訪問速度, 相應地服務器的開銷將增加.1為開啟,0為關閉.

DEBUG_MODE      :   0: 生成緩存文件,不強制編譯模板.1: 不生成緩存文件,不強制編譯模板. 2: 生成緩存文件, 強制編譯模板. 3: 不生成緩存文件, 強制編譯模版. 4: 生成緩存, 編譯模版但不生成編譯文件. 5: 不生成緩存, 編譯模版但不生成編譯文件.

CACHE_SERVER    :   數據緩存服務器,可以是default(php文件緩存),也可以是memcache

MEMBER_TYPE     :   可選值: default(使用內置的用戶系統),uc(使用UCenter做為用戶系統), 也可以是任意的第三方系統, 前提是您做好了相關的擴展程序:)
*/

return {%CONFIG_ARRAY%};

?>