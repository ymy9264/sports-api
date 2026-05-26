<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;



class Auth extends BaseController
{
    public function login(Request $request){
        $username = $request->param('username');
        $password = $request->param('password');

        $login_check = Db::table('user')->where('username',$username)->where('password',$password)->find();
        $key = 'youmeng_sports_admin_secret_key_2026';

        if($login_check){
            $permissions = Db::table('permission')->where('user_id',$login_check['id'])->column('module');
            $type = $login_check['type'];
            $payload = [
                'id' => $login_check['id'],
                'username' => $login_check['username'],
                'type' => $type,
                'exp' => time() + 86400  // 24小时过期
            ];
            $code = 0;
            $token = JWT::encode($payload, $key, 'HS256');
        }
        else{
            $code = 1;
            $token = '';
            $permissions = [];
            $type = '';
        }

        $res = array('code'=>$code,'token'=>$token,'permissions'=>$permissions,'type'=>$type);
        return json($res);
    }
}