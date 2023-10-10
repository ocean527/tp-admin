<?php


/**
 * 构造layUI数据格式
 *
 * @author ocean
 */

namespace app\common\model;
use think\Response;
use think\exception\HttpResponseException;

class LayUI {
    
    public static function table($count, $data, $msg = '') {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'count' => $count,
            'data' => $data,
        ];

        $response = Response::create($result, 'json');
        throw new HttpResponseException($response);
    }
    
    public static function selectOptions($data) {
        $response = Response::create($data, 'json');
        throw new HttpResponseException($response);
    }
}