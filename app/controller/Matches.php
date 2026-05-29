<?php

namespace app\controller;

use app\BaseController;

use app\Request;
use think\facade\Db;

class Matches extends BaseController
{
    public function index(Request $request){
        $page = $request->param('page',1);
        $pageSize = $request->param('pageSize',20);

        $keyword = $request->param('keyword', '');
        $query = Db::table('matches')
            ->where('league.is_main',1)
            ->join('league','matches.name=league.name');

        if ($keyword) {
            $query = $query->whereLike('league.name', "%$keyword%")
                ->whereOr('home_team', 'like', "%$keyword%")
                ->whereOr('visit_team', 'like', "%$keyword%");
        }

        $total = $query->count();
        $list = $query->field('matches.*')->page($page, $pageSize)->select();
        return json(['total'=>$total,'list'=>$list]);
    }

    public function save(Request $request){
        $name = $request->param('name');
        $home_team = $request->param('home_team');
        $visit_team = $request->param('visit_team');
        $match_time = $request->param('match_time');
        $score = $request->param('score');
        $db_data = array(
            'name'=>$name,
            'home_team'=>$home_team,
            'visit_team'=>$visit_team,
            'match_time'=>$match_time,
            'score'=>$score
        );
        $insert = Db::table('matches')->insert($db_data);
        $db_status = $insert ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function update(Request $request){
        $id = $request->param('id');
        $name = $request->param('name');
        $home_team = $request->param('home_team');
        $visit_team = $request->param('visit_team');
        $match_time = $request->param('match_time');
        $score = $request->param('score');

        $db_data = array(
            'name'=>$name,
            'home_team'=>$home_team,
            'visit_team'=>$visit_team,
            'match_time'=>$match_time,
            'score'=>$score
        );
        $update = Db::table('matches')->where('id',$id)->update($db_data);
        $db_status = $update ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);
    }

    public function delete(Request $request){
        $id = $request->param('id');
        $delete = Db::table('matches')->where('id',$id)->delete();
        $db_status = $delete ? 0 : 1;

        $return = array('code'=>$db_status);
        return json($return);

    }
}