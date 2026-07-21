<?php
namespace app\controller;

use app\BaseController;
use think\facade\Cache;

class Test extends BaseController
{
    public function redisTest()
    {
        Cache::set('test_key', 'hello redis', 60);
        $value = Cache::get('test_key');

        return json([
            'value' => $value,
            'redis_ok' => $value === 'hello redis'
        ]);
    }
}