<?php


namespace app\controller;

use app\BaseController;
use think\facade\Db;
use Symfony\Component\DomCrawler\Crawler;
set_time_limit(0);
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

    function create_randomstr($length, $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
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
                $temp = explode('日', $match_time);

                $matchDay = (int)$temp[0];  // 31、1、2
                if (count($temp) < 2) {
                    return;
                }

                // 月末跨月处理
                $day = (int)date('d', strtotime('-1 day'));
                $baseTime = strtotime('-1 day');

                if ($day >= 28 && in_array($matchDay, [1, 2])) {
                    $pageDate = date('Y-m', strtotime('+1 month', $baseTime));
                } else {
                    $pageDate = date('Y-m', $baseTime);
                }

                $match_time = $pageDate . '-' . $match_time . ':00';
                $timestamp = strtotime($match_time);

                if (
                    $timestamp === false ||
                    date('Y-m-d H:i:s', $timestamp) !== $match_time
                ) {
                    echo "非法时间：".$match_time.PHP_EOL;
                    return;
                }
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

     function flags(){
        $json = '[
  {"id":819,"name":"墨西哥","flag":"https://zq.titan007.com/Image/team/images/1kd9t4bkpnb.png"},
  {"id":803,"name":"南非","flag":"https://zq.titan007.com/Image/team/images/1kd9sw00w6d.png"},
  {"id":898,"name":"韩国","flag":"https://zq.titan007.com/Image/team/images/1kd9t7mybnb.png"},
  {"id":747,"name":"捷克","flag":"https://zq.titan007.com/Image/team/images/16498366963.png"},
  {"id":795,"name":"加拿大","flag":"https://zq.titan007.com/Image/team/images/1kd9te475h31.png"},
  {"id":782,"name":"波黑","flag":"https://zq.titan007.com/Image/team/images/1krg0gedq426.png"},
  {"id":797,"name":"美国","flag":"https://zq.titan007.com/Image/team/images/1kd9x10rpm2a.png"},
  {"id":776,"name":"巴拉圭","flag":"https://zq.titan007.com/Image/team/images/1kd9x06mhg1c.png"},
  {"id":904,"name":"卡塔尔","flag":"https://zq.titan007.com/Image/team/images/1kd9tjqr2b13.png"},
  {"id":648,"name":"瑞士","flag":"https://zq.titan007.com/Image/team/images/1kd9tcnft7h.png"},
  {"id":778,"name":"巴西","flag":"https://zq.titan007.com/Image/team/images/1kd9wy18gb2t.png"},
  {"id":813,"name":"摩洛哥","flag":"https://zq.titan007.com/Image/team/images/1kd4ndnmtw17.png"},
  {"id":909,"name":"海地","flag":"https://zq.titan007.com/Image/team/images/1kd9wz49sw2b.png"},
  {"id":641,"name":"苏格兰","flag":"https://zq.titan007.com/Image/team/images/1kd9tmd68p1q.png"},
  {"id":913,"name":"澳大利亚","flag":"https://zq.titan007.com/Image/team/images/1kd9x2rd7q7.png"},
  {"id":762,"name":"土耳其","flag":"https://zq.titan007.com/Image/team/images/164984190591.png"},
  {"id":650,"name":"德国","flag":"https://zq.titan007.com/Image/team/images/1kd9x52q0c0.png"},
  {"id":17976,"name":"库拉索","flag":"https://zq.titan007.com/Image/team/images/1kd9x6zjk8t.png"},
  {"id":646,"name":"荷兰","flag":"https://zq.titan007.com/Image/team/images/1kd9x92yqm14.png"},
  {"id":903,"name":"日本","flag":"https://zq.titan007.com/Image/team/images/1kd9xadskf2v.png"},
  {"id":809,"name":"科特迪瓦","flag":"https://zq.titan007.com/Image/team/images/1kd59km201y.png"},
  {"id":779,"name":"厄瓜多尔","flag":"https://zq.titan007.com/Image/team/images/1kd9x5x0yg1h.png"},
  {"id":644,"name":"瑞典","flag":"https://zq.titan007.com/Image/team/images/164983505795.png"},
  {"id":823,"name":"突尼斯","flag":"https://zq.titan007.com/Image/team/images/1kd4y40jnv1v.png"},
  {"id":772,"name":"西班牙","flag":"https://zq.titan007.com/Image/team/images/1kd9trgscek.png"},
  {"id":790,"name":"佛得角","flag":"https://zq.titan007.com/Image/team/images/1kd9tt61s01t.png"},
  {"id":645,"name":"比利时","flag":"https://zq.titan007.com/Image/team/images/1kd9szmt7c1b.png"},
  {"id":735,"name":"埃及","flag":"https://zq.titan007.com/Image/team/images/1kd9t2vfd01a.png"},
  {"id":891,"name":"沙特阿拉伯","flag":"https://zq.titan007.com/Image/team/images/1kd9tvn8rc1h.png"},
  {"id":767,"name":"乌拉圭","flag":"https://zq.titan007.com/Image/team/images/1kd9twt0q412.png"},
  {"id":783,"name":"伊朗","flag":"https://zq.titan007.com/Image/team/images/1kd9t4tft00.png"},
  {"id":2363,"name":"新西兰","flag":"https://zq.titan007.com/Image/team/images/1kd9t67nxv21.png"},
  {"id":649,"name":"法国","flag":"https://zq.titan007.com/Image/team/images/1kda1tadyh9.png"},
  {"id":815,"name":"塞内加尔","flag":"https://zq.titan007.com/Image/team/images/1kd59fqdks1t.png"},
  {"id":874,"name":"伊拉克","flag":"https://zq.titan007.com/Image/team/images/165018992317.png"},
  {"id":640,"name":"挪威","flag":"https://zq.titan007.com/Image/team/images/1kda1rwfg32y.png"},
  {"id":766,"name":"阿根廷","flag":"https://zq.titan007.com/Image/team/images/1kda1y9gfy1d.png"},
  {"id":18406,"name":"阿尔及利亚","flag":"https://zq.titan007.com/Image/team/images/1kd4yh55v51h.png"},
  {"id":647,"name":"奥地利","flag":"https://zq.titan007.com/Image/team/images/1kda1wqsej1c.png"},
  {"id":881,"name":"约旦","flag":"https://zq.titan007.com/Image/team/images/1kda1znxnf0.png"},
  {"id":765,"name":"葡萄牙","flag":"https://zq.titan007.com/Image/team/images/1kda23vp7420.png"},
  {"id":811,"name":"刚果民主共和国","flag":"https://zq.titan007.com/Image/team/images/1kd4n7rc8r1p.png"},
  {"id":744,"name":"英格兰","flag":"https://zq.titan007.com/Image/team/images/1kda293ej631.png"},
  {"id":768,"name":"克罗地亚","flag":"https://zq.titan007.com/Image/team/images/1kda2amc8t28.png"},
  {"id":810,"name":"加纳","flag":"https://zq.titan007.com/Image/team/images/1kda2e0fjvt.png"},
  {"id":798,"name":"巴拿马","flag":"https://zq.titan007.com/Image/team/images/1kda2btvdd16.png"},
  {"id":875,"name":"乌兹别克斯坦","flag":"https://zq.titan007.com/Image/team/images/1kda27jxznk.png"},
  {"id":775,"name":"哥伦比亚","flag":"https://zq.titan007.com/Image/team/images/1kda25g1132q.png"}
]';


        $a = json_decode($json,true);
        $dir = '/public/ft_logo/75/';

        var_dump(is_dir($dir));

        if(is_dir($dir)){
            print_r(scandir($dir));
        }

        foreach($a as $row){

            $path = 'D:/ucs_dev/public/ft_logo/75/'.$row['id'].'.png';
            $url = $row['flag'];

            $content = $this->https_request($url);

            if($content === false){
                echo $row['name'].' 下载失败<br>';
                continue;
            }

            $result = file_put_contents($path,$content);

            if($result === false){
                echo $row['name'].' 保存失败<br>';
            }else{
                echo $row['name'].' 保存成功('.$result.'字节)<br>';
            }
        }
    }

    public function run(){


        $start_date = date('Y-m-d',strtotime('-2 days'));
        $end_date = date('Y-m-d');

        $page_list = range(1,3,1);
        foreach($page_list as $page) {
            //   $url = 'https://webapi.sporttery.cn/gateway/jc/basketball/getMatchResultV1.qry?matchPage=1&matchBeginDate='.$start_date.'&matchEndDate='.$end_date.'&leagueId=&pageSize=30&pageNo='.$page.'&isFix=0&pcOrWap=1';
            $url = 'https://webapi.sporttery.cn/gateway/uniform/basketball/getUniformMatchResultV2.qry?matchPage=1&matchBeginDate='.$start_date.'&matchEndDate='.$end_date.'&leagueId=&pageSize=30&pageNo='.$page.'&isFix=0&pcOrWap=1';

            $nodePath = '"d:/nodejs/node.exe"';  // Node.js 完整路径
            $jsPath = '"d:/ucs_dev/public/1.js"';                     // Node.js 脚本完整路径
            $outputFile = $start_date.'-'.$end_date.'.html';

            // 捕获标准输出和错误
            $cmd = "$nodePath $jsPath " . escapeshellarg($url) . " $outputFile 2>&1";
            $output = shell_exec($cmd);
            dump($output);
            $content = file_get_contents($outputFile);

            $content = json_decode($content,true);
            dump($content);
            exit;
            $match_list = $content['value']['matchResult'];

            foreach($match_list as $row){
                if($row['status'] == 2){

                    $score = $row['finalScore'];
                    $score = explode(':',$score);
                    $home = $score[1];
                    $visit = $score[0];
                    $score_total = $home + $visit;
                    $score_gap = $home - $visit;
                    if($score_gap > 0){
                        $result = 3;
                    }
                    else if($score_gap == 0){
                        $result = 2;
                    }
                    else if($score_gap < 0){
                        $result = 1;
                    }

                    $db_data = array(
                        'score'=>$row['finalScore'],
                        'score_total'=>$score_total,
                        'score_gap'=>$score_gap,
                        'result'=>$result
                    );
                    $this->db->where('jc_match_id',$row['matchId'])->update('bk_jc_matches',$db_data);
                }
                else if($row['matchResultStatus'] == 0){
                    $db_data = array(
                        'score'=>'取消',
                    );
                    $this->db->where('jc_match_id',$row['matchId'])->update('bk_jc_matches',$db_data);
                }
            }

        }
    }



}