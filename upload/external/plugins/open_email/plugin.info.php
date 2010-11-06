<?php

return array(
    'id' => 'open_email',
    'hook' => 'after_opening',
    'name' => '開店郵件通知',
    'desc' => '開店成功後給店主發郵件通知',
    'author' => 'ECMall Team',
    'version' => '1.0',
    'config' => array(
        'subject' => array(
            'type' => 'text',
            'text' => '郵件標題'
        ),
        'content' => array(
            'type' => 'textarea',
            'text' => '郵件內容'
        )
    )
);

?>