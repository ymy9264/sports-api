<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Teams extends BaseController
{
    public function index(){
        $db_data = Db::table('team')->select();
        return json($db_data);
    }
}