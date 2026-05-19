<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;

class User extends BaseController
{
    public function index(){
        $db_data = Db::table('user')->where('type','normal')->field('id,username,type,status')->select();
        return json($db_data);
    }

    public function save(Request $request){
        $username = $request->param('username');
        $password = $request->param('password');
        $db_data = array(
            'username'=>$username,
            'password'=>$password,
            'type'=>'normal',
            'status'=>1
        );
        $insert = Db::table('user')->insert($db_data);
        $db_status = $insert ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function update(Request $request){
        $id = $request->param('id');
        $username = $request->param('username');
        $password = $request->param('password');
        $db_data = array(
            'username'=>$username,
            'type'=>'normal',
            'status'=>1
        );
        if($password){
            $db_data['password'] = $password;
        }
        $update = Db::table('user')->where('id',$id)->update($db_data);
        $db_status = $update ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function delete(Request $request){
        $id = $request->param('id');
        $delete = Db::table('user')->where('id',$id)->delete();
        $db_status = $delete ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);

    }

    public function toggle(Request $request){
        $id = $request->param('id');
        $user = Db::table('user')->where('id', $id)->find();
        $newStatus = $user['status'] == 1 ? 0 : 1;
        $toggle = Db::table('user')->where('id', $id)->update(['status' => $newStatus]);
        $db_status = $toggle ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);

    }
}