<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;

class Players extends BaseController
{
    public function index(Request $request){
        $page = $request->param('page',1);
        $pageSize = $request->param('pageSize',20);

        $keyword = $request->param('keyword', '');
        $query = Db::table('player');

        if ($keyword) {
            $query = $query->whereLike('name', "%$keyword%")
                ->whereOr('position', 'like', "%$keyword%")
                ->whereOr('nationality', 'like', "%$keyword%")
                ->whereOr('team', 'like', "%$keyword%");
        }

        $total = $query->count();
        $list = $query->page($page, $pageSize)->select();
        return json(['total'=>$total,'list'=>$list]);
    }

    public function save(Request $request){
        $name = $request->param('name');
        $team = $request->param('team');
        $position = $request->param('position');
        $number = $request->param('number');
        $nation = $request->param('nationality');
        $birthday = $request->param('birthday');
        $db_data = array(
            'name'=>$name,
            'team'=>$team,
            'position'=>$position,
            'number'=>$number,
            'nationality'=>$nation,
            'birthday'=>$birthday
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
        $position = $request->param('position');
        $number = $request->param('number');
        $nation = $request->param('nationality');
        $birthday = $request->param('birthday');
        $db_data = array(
            'name'=>$name,
            'team'=>$team,
            'position'=>$position,
            'number'=>$number,
            'nationality'=>$nation,
            'birthday'=>$birthday
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