<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Players extends BaseController
{
    public function index(){
        $db_data = Db::table('player')->select();
        return json($db_data);
    }
}