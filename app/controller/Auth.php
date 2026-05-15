<?php

namespace app\controller;

use app\BaseController;
use app\Request;

class Auth extends BaseController
{
    public function login(Request $request){
        $username = $request->param('username');
        $password = $request->param('password');
        if($username === 'admin' && $password === '123456'){
            $code = 0;
        }
        else{
            $code = 1;
        }

        $res = array('code'=>$code);
        return json($res);
    }
}