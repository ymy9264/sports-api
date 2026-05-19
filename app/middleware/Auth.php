<?php
namespace app\middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return json(['code' => 401, 'msg' => '未登录'], 401);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $key = 'youmeng_sports_admin_secret_key_2026';
            JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return json(['code' => 401, 'msg' => 'Token无效或已过期'], 401);
        }

        return $next($request);
    }
}