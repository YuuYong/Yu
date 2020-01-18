<?php

// 路由示例
return [
    '' => 'TestController@html', // example.com
    'redis' => 'TestController@redis@post', // example.com/redis
    'mysql' => 'TestController@mysql@post', // example.com/mysql
    'http' => 'TestController@http', // example.com/http
    'jsonp' => 'TestController@info', // example.com/jsonp
    'api' => [
        'user' => [
            'info' => 'TestController@test' // example.com/api/user/info
        ],
        'admin' => 'AdminController@test' // example.com/api/admin
    ],
    'data/foo' => 'TestController@test', // example.com/data/foo
    'html' => 'TestController@html', // example.com/html
    'file' => 'TestController@files', // example.com/file
    'video' => 'TestController@video', // example.com/video
];