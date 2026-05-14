<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;

class Players extends BaseController
{
    public function index(){
        $db_data = Db::table('player')->select();
        return json($db_data);
    }

    public function save(Request $request){
        $name = $request->param('name');
        $team = $request->param('team');
        $time = $request->param('position');
        $status = $request->param('number');
        $nation = $request->param('nationality');
        $age = $request->param('age');
        $db_data = array(
            'name'=>$name,
            'team'=>$team,
            'time'=>$time,
            'status'=>$status,
            'nationality'=>$nation,
            'age'=>$age
        );
        $insert = Db::table('player')->insert($db_data);
        $db_status = $insert ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function update(Request $request){
        $id = $request->param('id');
        $name = $request->param('name');
        $team = $request->param('team');
        $time = $request->param('position');
        $status = $request->param('number');
        $nation = $request->param('nationality');
        $age = $request->param('age');
        $db_data = array(
            'name'=>$name,
            'team'=>$team,
            'time'=>$time,
            'status'=>$status,
            'nationality'=>$nation,
            'age'=>$age
        );
        $update = Db::table('player')->where('id',$id)->update($db_data);
        $db_status = $update ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function delete(Request $request){
        $id = $request->param('id');
        $delete = Db::table('player')->where('id',$id)->delete();
        $db_status = $delete ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);

    }
}