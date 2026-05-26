<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Dashboard extends BaseController
{
    public function index(){
        $match = Db::table('matches')->count();
        $player = Db::table('player')->count();
        $team = Db::table('team')->count();
        $today = date('Y-m-d 00:00:00');
        $todayMatch = Db::table('matches')
            ->where('league.is_main',1)
            ->join('league','matches.name=league.name')
            ->where('match_time','>=',$today)
            ->count();
        $todayLeague = Db::table('matches')
            ->field('matches.name,count(*) as total')
            ->where('league.is_main',1)
            ->join('league','matches.name=league.name')
            ->where('match_time','>=',$today)
            ->group('name')
            ->select();

        $data = array(
            'match'=>$match,
            'player'=>$player,
            'team'=>$team,
            'todayMatch'=>$todayMatch,
            'todayLeague'=>$todayLeague
        );

        return json($data);
    }
}