<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use Symfony\Component\DomCrawler\Crawler;

class dataCrawler extends BaseController
{
    private function https_request($url, $data=null, $time=600, $referer='',$cookie=''){
        $curl_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36';
        $headers = array(
            'accept:text/html',
            'sec-ch-ua:"Chromium";v="136", "Google Chrome";v="136", "Not.A/Brand";v="99"',
            'sec-ch-ua-mobile:?0',
            'sec-ch-ua-platform:Windows',
            'Cache-Control: public, must-revalidate, max-age=60'
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
        $url = 'https://bf.titan007.com/football/Over_20260514.htm';
        $content = $this->https_request($url);

        echo mb_detect_encoding(
            $content,
            ['UTF-8', 'GBK', 'GB2312', 'BIG5'],
            true
        );
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
                $match_time = str_replace('日',' ',$match_time);
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

                $check = Db::table('match')->where('qt_match_id',$qt_match_id)->find();
                if (!$check){
                    Db::table('match')->insert($info);
                }else{
                    Db::table('match')->where('qt_match_id',$qt_match_id)->update($info);
                }
            }
        });



    }
}