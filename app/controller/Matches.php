<?php

namespace app\controller;

use app\BaseController;

use think\facade\Db;

class Matches extends BaseController
{
    public function index(){
        $db_data = Db::table('match')->select();
        return json($db_data);
    }
}