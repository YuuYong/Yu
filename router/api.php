<?php

// 路由示例
return [
    '' => 'TestController@test', // example.com
    'api' => [
        'user' => [
            'info' => 'TestController@test' // example.com/api/user/info
        ],
        'admin' => 'AdminController@test' // example.com/api/admin
    ],
    'data/foo' => 'TestController@test', // example.com/data/foo
];