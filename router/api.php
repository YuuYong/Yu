<?php

// 路由示例
return [
    '' => 'TestController@html', // example.com
    'redis' => 'TestController@redis', // example.com
    'mysql' => 'TestController@mysql', // example.com
    'http' => 'TestController@http', // example.com
    'api' => [
        'user' => [
            'info' => 'TestController@test' // example.com/api/user/info
        ],
        'admin' => 'AdminController@test' // example.com/api/admin
    ],
    'data/foo' => 'TestController@test', // example.com/data/foo
];