<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if( !function_exists("tree_array")){
    /**
     * 输出树形数组（父亲找孩子）
     * @param array $arr
     * @param integer $pid
     * @return array
     */
    function tree_array($arr = [], $pid = 0, $childrenKey = 'children'){
        $newArr = [];
        foreach ($arr as $val){
            if($val['pid'] == $pid){
                $val[$childrenKey] = tree_array($arr,$val['id'],$childrenKey);
                $val['checked'] = false;
                $val['open'] = true;
                if (count($val[$childrenKey]) == 0) {
                    unset($val[$childrenKey]);
                }
                $newArr[] = $val;
            }            
        }
        return $newArr;
    }
}

if( !function_exists('tree_grid')){
    /**
     * 生成用于treegrid插件的树形数据
     * @staticvar array $newArr
     * @param array $arr
     * @param array $pid
     * @return type
     */
    function tree_grid($arr = [], $pid = 0){
        static $newArr = [];
        foreach ($arr as $val){
            if($val['pid'] == $pid){
                $newArr[] = $val;
                tree_grid($arr,$val['id']);                
            }            
        }
        return $newArr;
    }
}

/**
 * 模拟post请求
 * @param $url
 * @param $data
 * @return mixed
 */
function post($url,$data) {
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_NOBODY, 1);
    curl_setopt($process, CURLOPT_POST, 1);
    curl_setopt($process, CURLOPT_POSTFIELDS, $data);
    $return = curl_exec($process);
    curl_close($process);
    return $return;
}

/**
 * 发送socket消息
 * @param $data
 * @return bool
 */
function sendSocketMsg(array $data)
{
    if(!is_array($data)) return false;
    $str = http_build_query($data);
    $url = "http://" . config('socket.socket_host') . ":" . config('socket.socket_http_port');
    return post($url, $str);
}


/**
 * 生产用户bootstrap treeview插件数据
 * @param type $arr
 * @param type $pid
 * @return type
 */
function tree_view($arr = [], $pid = 0, $checkedArr = array()){
    $newArr = [];
    foreach ($arr as $val){
        if(in_array($val['id'],$checkedArr)){
            $val['state'] = ['checked'=>true,'selected'=>true];
        }
        if($val['pid'] == $pid){
            $val['nodes'] = tree_view($arr,$val['id'],$checkedArr);
            $newArr[] = $val;
        }            
    }
    return $newArr;
}

/**
 * 生成树形缩进分类
 * @staticvar array $arrCat
 * @param array $data
 * @param integer $pid
 * @param integer $level
 * @return array
 */
function generate_tree_cate($data, $pid = 0, $level = 0) {
    if($level == 10) return null;
    $l = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level) . '└';
    static $arrCat    = array();
    $arrCat    = empty($level) ? array() : $arrCat;
    foreach($data as $k => $row) {
        if($row['pid'] == $pid) {
            //如果当前遍历的id不为空
            $row['text']    = $l . $row['text'];
            $row['level']    = $level;
            $arrCat[]    = $row;
            generate_tree_cate($data, $row['id'], $level+1);//递归调用
        }
    }
    return $arrCat;
}

if( !function_exists('tree_menu')){
    function tree_menu($arr, $level = 0){
        $menu = $level > 0 ? array('','','') : array('<ul class="treeview-menu">','','</ul>');
        foreach ($arr as $val){
            $menu[1] .= '<li class="treeview"><a href="javascript:;"><i class="fa fa-dashboard"></i> <span>'.$val['text'].'</span> <i class="fa fa-angle-left pull-right"></i></a></li>';
        }
    }
}

function password_md5($password){
    return md5(md5($password).config('crypt_key'));
}

function unique_str($len){
    return uniqid();
}

function camelize($uncamelized_words,$separator='_')
{
    $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
    return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
}

function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

function is_https() {
    if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
        return true;
    } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}

/**
 * 车牌号码验证
 * @param $license
 * @return bool
 */
function isCarLicense($license)
{
    if (empty($license)) {
        return false;
    }
    #匹配民用车牌和使馆车牌
    # 判断标准
    # 1，第一位为汉字省份缩写
    # 2，第二位为大写字母城市编码
    # 3，后面是5位仅含字母和数字的组合
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #匹配特种车牌(挂,警,学,领,港,澳)
    $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    } #匹配武警车牌

    $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #匹配军牌
    $regular = "/[A-Z]{2}[0-9]{5}$/";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #匹配新能源车辆6位车牌
    #小型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #大型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    return false;
}

function base64StrToImage($base64str, $dir = "") {
    $fileDir = PUBLIC_PATH . "public/" . $dir . "/" . date("Y") . "/" .date("m") . "/" . date("d") . "/";    
    if (!is_dir($fileDir)) {
        mkdir($fileDir, 0777, true);
    }
    $filePath = $fileDir . md5($base64str) . ".jpg";
    if (file_put_contents($filePath, base64_decode($base64str)) !== false) {
        return str_replace(PUBLIC_PATH, "/", $filePath);
    } else {
        return "";
    }
}

/**
 * 判断两个矩形是否交叉
 * @param $c1 参数格式[x1,y1,x2,y2] 矩形对角坐标
 * @param $c2
 * @return bool
 */
function mat_inter($c1, $c2)
{
    list($x01, $y01, $x02, $y02) = $c1;
    list($x11, $y11, $x12, $y12) = $c2;
    $lx = abs(($x01 + $x02) / 2 - ($x11 + $x12) / 2);
    $ly = abs(($y01 + $y02) / 2 - ($y11 + $y12) / 2);
    $sax = abs($x01 - $x02);
    $sbx = abs($x11 - $x12);
    $say = abs($y01 - $y02);
    $sby = abs($y11 - $y12);
    if ($lx <= ($sax + $sbx) / 2 and $ly <= ($say + $sby) / 2) {
        return true;
    } else {
        return false;
    }
}

/**
 * 计算两个矩形的交叉面积
 * @param $c1 参数格式[x1,y1,x2,y2] 矩形对角坐标
 * @param $c2
 * @return float|int
 */
function solve_coincide($c1, $c2)
{
    if (mat_inter($c1, $c2)) {
        list($x01, $y01, $x02, $y02) = $c1;
        list($x11, $y11, $x12, $y12) = $c2;
        $col = min($x02, $x12) - max($x01, $x11);
        $row = min($y02, $y12) - max($y01, $y11);
        $intersection = $col * $row;
        $area1 = ($x02 - $x01) * ($y02 - $y01);
        $area2 = ($x12 - $x11) * ($y12 - $y11);
        $coincide = $intersection / ($area1 + $area2 - $intersection);
        return $coincide;
    } else {
        false;
    }
}


function retJson($errorCode = 0, $msg = "" , $data = []) {
    return json_encode([
        "error_code" => $errorCode,
        "msg" => $msg,
        "data" => $data
    ]);
}

/**
 * 判断矩形是否包含另一个矩形
 * @param array $c1 大矩形坐标
 * @param array $c2 小矩形坐标
 * @return boolean
 */
function isRectContainRect($c1, $c2) {
    list($x01, $y01, $x02, $y02) = $c1;
    list($x11, $y11, $x12, $y12) = $c2;
    if ($x01 <= $x11 && $y01 <= $y11 && $x02 >= $x12 && $y02 >= $y12) {
        return true;
    } else {
        return false;
    }
}

/**
 * 车牌比较
 * @param str $carplate1
 * @param str $carplate2
 * @return int
 */
function carplateDiff($carplate1, $carplate2) {
    $len1 = mb_strlen($carplate1);
    $len2 = mb_strlen($carplate2);
    if ($len1 != $len2) {
        return -1;
    }

    $diffCounter = 0;
    for ($i = 0; $i < $len1; $i++) {
        $letter1 = mb_substr($carplate1, $i, 1);
        $letter2 = mb_substr($carplate2, $i, 1);
        if ($letter1 != $letter2) {
            $diffCounter++;
        }
    }
    return $diffCounter;
}

//通过企业微信webhook发送消息
function send_wxwork_webhook_msg($webhook_link, $msg) {
    $process = curl_init($webhook_link);
    $headers = [
        "Content-Type: application/json",
    ];
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($process, CURLOPT_NOBODY, 1);
    curl_setopt($process, CURLOPT_POST, 1);
    curl_setopt($process, CURLOPT_POSTFIELDS, json_encode([
        "msgtype" => "text",
        "text" => [
            "content" => $msg
        ]
    ]));
    $return = curl_exec($process);
    curl_close($process);
    $res = json_decode($return, true);
    if (isset($res["errcode"]) && $res["errcode"] == 0) {
        return true;
    } else {
        return false;
    }
}