<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use Symfony\Component\DomCrawler\Crawler;

class DataCrawler extends BaseController
{
    private function https_request($url, $data=null, $time=600, $referer='',$cookie=''){
        $curl_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36';
        $headers = array(
            'Accept: */*',
            'Accept-Language: zh-CN,zh;q=0.9',
            'Referer: https://zq.titan007.com/',
        );
        $cookie = '';

        $referer = '';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

        if($data){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        else{
            curl_setopt($curl,CURLOPT_ENCODING , "gzip");
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        if($referer){
            curl_setopt($curl,CURLOPT_REFERER,$referer);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $time);
        curl_setopt($curl, CURLOPT_USERAGENT, $curl_agent);

        if($cookie){
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }

        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    function matches(){
        $date = date('Ymd',strtotime('-1 day'));
        $url = 'https://bf.titan007.com/football/Over_'.$date.'.htm';
        $content = $this->https_request($url);

        echo mb_detect_encoding(
            $content,
            ['UTF-8', 'GBK', 'GB2312', 'BIG5'],
            true
        );
       // $content = mb_convert_encoding($content, 'UTF-8', 'GBK,GB2312,BIG5,UTF-8');
        $crawler = new Crawler($content);
        $crawler->filter('#table_live tr')->each(function ($node, $i) {
            if($i == 0){
                return;
            }
            $tds = $node->filter('td');
            if ($tds->count() >= 10) {
                $match_time = $node->filter('td')->eq(1)->text();
                $category = $node->filter('td')->eq(0)->text();
                $home_team = $node->filter('td')->eq(3)->text();
                $home_team = preg_replace('/\[.*?\]/', '', $home_team);
                $score = $node->filter('td')->eq(4)->text();
                $visit_team = $node->filter('td')->eq(5)->text();
                $visit_team = preg_replace('/\[.*?\]/', '', $visit_team);

                $onclick = $tds->eq(4)->attr('onclick');

                if ($onclick) {
                    $qt_match_id = str_replace(['showgoallist(', ')'], '', $onclick);
                }
                else{
                    return;
                }
                $match_time = str_replace('日', ' ', $match_time); // "24 10:40"

                $match_time = date('Y-m').'-'.$match_time.':00';
                $update = date('Y-m-d H:i:s');
                $info = array(
                    'qt_match_id'=>$qt_match_id,
                    'home_team' => $home_team,
                    'visit_team' => $visit_team,
                    'match_time' => $match_time,
                    'name' => $category,
                    'score' => $score,
                    'updatetime'=>$update
                );

                $check = Db::table('matches')->where('qt_match_id',$qt_match_id)->find();
                if (!$check){
                    Db::table('matches')->insert($info);
                }else{
                    Db::table('matches')->where('qt_match_id',$qt_match_id)->update($info);
                }
            }
        });
    }

    function players(){
        set_time_limit(0);
        $five_league = array('英超','德甲','意甲','西甲','法甲');
        $teams = Db::table('team')->whereIn('league',$five_league)->select();
        foreach($teams as $team_row){
            $team_id = $team_row['team_id'];
            $team_name = $team_row['team_cn'];
            $url = 'https://zq.titan007.com/jsData/teamInfo/teamDetail/tdl'.$team_id.'.js?version=2026052117';
            $content = $this->https_request($url);
            $content = mb_convert_encoding($content, 'UTF-8', 'GBK,GB2312,BIG5,UTF-8');
            if(!strpos($content,'var lineupDetail=[')){
                continue;
            }
            $players = '';
            $content = explode('];',$content);
            foreach($content as $content_item){
                if(strpos($content_item,'var lineupDetail=[')){
                    $players = $content_item;
                }
            }
            $players = str_replace('var lineupDetail=','',$players);
            $players = str_replace("'", '"', $players);
            $players = $players.']';
            $player_list = json_decode($players,true);
            dump($player_list);

            foreach($player_list as $player_item) {

                $db_data = array(
                    'number'=> trim($player_item[1]) ? : null,
                    'name' => $player_item[2],
                    'team'=> $team_name,
                    'position'=>$player_item[8],
                    'nationality'=>$player_item[9],
                    'qt_player_id'=>$player_item[0],
                    'qt_team_id'=>$team_id,
                    'birthday'=>$player_item[5]
                );
                $check = Db::table('player')->where('qt_player_id',$player_item[0])->find();
                if($check){
                    Db::table('player')->where('qt_player_id',$player_item[0])->update($db_data);
                }
                else{
                    Db::table('player')->insert($db_data);
                }
            }


        }

    function teams(){
        set_time_limit(0);
        $league = Db::table('league')->where('is_main',1)->select();
        foreach($league as $league_row) {

            $league_name = $league_row['name'];
            $qt_id = $league_row['qt_id'];
            $sub_id = $league_row['sub_id'];
            if ($sub_id === 0 || $sub_id === $qt_id) {
                $url = 'https://zq.titan007.com/jsData/matchResult/2025-2026/s' . $qt_id . '.js?version=2026052115';
            } else {
                $url = 'https://zq.titan007.com/jsData/matchResult/2025-2026/s' . $qt_id . '_' . $sub_id . '.js?version=2026052115';
            }
            $content = https_request($url);
            $content = mb_convert_encoding($content, 'UTF-8', 'GBK,GB2312,BIG5,UTF-8');
            echo $content;
            if (!strpos($content, 'var arrTeam = [')) {
                continue;
            }
            $content = explode('];', $content);

            dump($content);
            if ($sub_id === $qt_id) {
                $teams = $content[1] . ']';
            } else {
                $teams = $content[2] . ']';
            }
            $teams = str_replace('var arrTeam =', '', $teams);
            $teams = str_replace("'", '"', $teams);
            $team_list = json_decode($teams, true);
            if (!$team_list) {
                continue;
            }
            foreach ($team_list as $team_item) {

                $db_data = array(
                    'team_id' => $team_item[0],
                    'team_cn' => $team_item[1],
                    'team_en' => $team_item[3],
                    'league' => $league_name
                );
                $check = Db::table('team')->where('team_id', $team_item[0])->find();
                if ($check) {
                    Db::table('team')->where('team_id', $team_item[0])->update($db_data);
                } else {
                    Db::table('team')->insert($db_data);
                }
            }
        }


        }
    }
}