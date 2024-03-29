<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    '__rest__'=>[
        // 指向index模块的blog控制器
       // 'setting'=>'index/setting',
    ],
    'catUpdate' => ['index/setting/catUpdate', ['method' => 'post']],
    'cat'   => ['index/setting/cat', ['method' => 'get']],
    'catCreate' => ['index/setting/catCreate', ['method' => 'post']],
    //'supply' => ['index/purchase/supply', ['method' => 'get']],
];
