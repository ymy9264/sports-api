<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;

class Teams extends BaseController
{
    public function index(Request $request){
        $page = $request->param('page',1);
        $pageSize = $request->param('pageSize',20);
        $keyword = $request->param('keyword', '');
        $query = Db::table('team');

        if ($keyword) {
            $query = $query->whereLike('team_cn', "%$keyword%")
                ->whereOr('league', 'like', "%$keyword%")
                ->whereOr('team_en', 'like', "%$keyword%");
        }

        $total = $query->count();
        $list = $query->page($page, $pageSize)->select();
        return json(['total'=>$total,'list'=>$list]);
    }

    public function save(Request $request){
        $team_cn = $request->param('team_cn');
        $league = $request->param('league');
        $team_en = $request->param('team_en');
        $db_data = array(
            'team_cn'=>$team_cn,
            'league'=>$league,
            'team_en'=>$team_en
        );
        $insert = Db::table('team')->insert($db_data);
        $db_status = $insert ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function update(Request $request){
        $id = $request->param('id');
        $team_cn = $request->param('team_cn');
        $league = $request->param('league');
        $team_en = $request->param('team_en');
        $db_data = array(
            'team_cn'=>$team_cn,
            'league'=>$league,
            'team_en'=>$team_en
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