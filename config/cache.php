<?php
return [
    // 默认缓存驱动
    'default' => 'file',

    'stores' => [
        'file' => [
            'type'   => 'File',
            'path'   => '',
        ],

        'redis' => [
            'type'     => 'redis',
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'password' => '你设置的密码',
            'select'   => 0,
            'timeout'  => 0,
            'expire'   => 3600,
            'persistent' => false,
            'prefix'   => 'sports_',
        ],
    ],
];