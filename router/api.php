<?php

// 路由示例
return [
    '' => 'TestController@html', // example.com
    'redis' => 'TestController@redis', // example.com/redis
    'mysql' => 'TestController@mysql', // example.com/mysql
    'http' => 'TestController@http', // example.com/http
    'jsonp' => 'TestController@jsonp', // example.com/jsonp
    'api' => [
        'user' => [
            'info' => 'TestController@test' // example.com/api/user/info
        ],
        'admin' => 'AdminController@test' // example.com/api/admin
    ],
    'data/foo' => 'TestController@test', // example.com/data/foo
];