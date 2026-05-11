<?php

namespace app\controller;

use app\BaseController;

use app\Request;
use think\facade\Db;

class Matches extends BaseController
{
    public function index(){
        $db_data = Db::table('match')->select();
        return json($db_data);
    }

    public function save(Request $request){
        $name = $request->param('name');
        $team = $request->param('team');
        $time = $request->param('time');
        $status = $request->param('status');
        $db_data = array(
            'name'=>$name,
            'team'=>$team,
            'time'=>$time,
            'status'=>$status
        );
        $insert = Db::table('match')->insert($db_data);
        $db_status = $insert ? 1 : 0;

        $return = array('code'=>$db_status);
        return json($return);
    }
}