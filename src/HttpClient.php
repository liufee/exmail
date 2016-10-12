<?php

namespace Feehi;

class HttpClient{

    public static function post($url, $data, $header)
    {
        if( !function_exists('curl_init') ){
            throw new Exception("CURL should be installed");
        }
        $post_data = '';
        foreach($data as $k => $v){
            if( is_array($v) ){
                foreach($v as $key => $val){
                    $post_data .=  $k . '=' . $val . '&';
                }
            }else {
                $post_data .=  $k . '=' . $v . '&';
            }
        }
        $post_data = rtrim($post_data, '&');
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        if($header) curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        $result = curl_exec ($ch);
        curl_close($ch);
        return $result;
    }
}