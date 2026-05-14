<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;

class Teams extends BaseController
{
    public function index(){
        $db_data = Db::table('team')->select();
        return json($db_data);
    }

    public function save(Request $request){
        $name = $request->param('name');
        $league = $request->param('league');
        $city = $request->param('city');
        $coach = $request->param('coach');
        $founded = $request->param('founded');
        $db_data = array(
            'name'=>$name,
            'league'=>$league,
            'city'=>$city,
            'coach'=>$coach,
            'founded'=>$founded
        );
        $insert = Db::table('team')->insert($db_data);
        $db_status = $insert ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function update(Request $request){
        $id = $request->param('id');
        $name = $request->param('name');
        $league = $request->param('league');
        $city = $request->param('city');
        $coach = $request->param('coach');
        $founded = $request->param('founded');
        $db_data = array(
            'name'=>$name,
            'league'=>$league,
            'city'=>$city,
            'coach'=>$coach,
            'founded'=>$founded
        );
        $update = Db::table('team')->where('id',$id)->update($db_data);
        $db_status = $update ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function delete(Request $request){
        $id = $request->param('id');
        $delete = Db::table('team')->where('id',$id)->delete();
        $db_status = $delete ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);

    }
}