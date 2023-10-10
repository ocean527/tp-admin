<?php

/**
 * Description of Article
 *
 * @author ocean
 */

namespace app\common\model;
use think\Model;
use app\common\model\AdminCommonTrait;
use think\facade\Cache;

class Config extends Model {
    use AdminCommonTrait;
    
    const CONFIG_GROUP_BASE = 1;    //基本配置
    const CONFIG_GROUP_CONTENT = 2; //内容配置
    const CONFIG_GROUP_USER = 3;    //用户配置
    const CONFIG_GROUP_SYSTEM = 4;  //系统配置
    
    const CONFIG_TYPE_NUMBER = 1;   //数字
    const CONFIG_TYPE_STRING = 2;   //字符
    const CONFIG_TYPE_TEXT = 3;     //文本
    const CONFIG_TYPE_ARRAY = 4;    //数组
    const CONFIG_TYPE_ENUM = 5;     //枚举
    
    public static $groupArr = [
        self::CONFIG_GROUP_BASE => '基本',
        self::CONFIG_GROUP_CONTENT => '内容',
        self::CONFIG_GROUP_USER => '用户',
        self::CONFIG_GROUP_SYSTEM => '系统'
    ];
    
    public static $typeArr = [
        self::CONFIG_TYPE_NUMBER => '数字',
        self::CONFIG_TYPE_STRING => '字符',
        self::CONFIG_TYPE_TEXT => '文本',
        self::CONFIG_TYPE_ARRAY => '数组',
        self::CONFIG_TYPE_ENUM => '枚举'
    ];
    
    public function paramPreprocess($query, $params) {
        $query->withAttr('config_group_text', [$this, 'getConfigGroupText']);
        $query->withAttr('config_type_text', [$this, 'getConfigTypeText']);
    }
    
    public function getConfigGroupText($value, $data) {    
        return self::$groupArr[$data['group']];
    }
    
    public function getConfigTypeText($value, $data) {
        return self::$typeArr[$data['type']];
    }
    
    public function loadConfig(){
        $configItem = Cache::get("configItem");
        if ($configItem) {
            config($configItem,'extra');
        } else {
            $configs = $this->order('orderby asc')->select();
            $configItem = [];
            foreach ($configs->toArray() as $item) {
                if ($item['type'] == self::CONFIG_TYPE_ARRAY) {
                    $configArr = explode(",",$item['value']);
                    $configValue = [];
                    foreach ($configArr as $arr) {
                        $splitArr = explode(":",$arr);
                        $configValue[$splitArr[0]] = $splitArr[1];
                    }
                    $configItem[$item['name']] = $configValue;
                } else {
                    if ($item['value'] == "") {
                        continue;
                    }
                    switch ($item['type']) {
                        case self::CONFIG_TYPE_NUMBER:  //数字
                            $configItem[$item['name']] = floatval($item['value']);
                            break;
                        case self::CONFIG_TYPE_STRING:
                        case self::CONFIG_TYPE_TEXT:  //字符
                        case self::CONFIG_TYPE_ENUM:
                            $configItem[$item['name']] = strval($item['value']);
                            break;
                    }
                }
            }
            if ($configItem) {
                config($configItem,'extra');
                Cache::set("configItem",$configItem,0);
            }
        }
    }
    
    public function getConfigItems() {
        $configItems = $this->order("group asc")->select()->toArray();
        $configItemsArr = [];
        foreach ($configItems as $row) {
            if ($row['type'] == self::CONFIG_TYPE_ARRAY) {
                continue;
            }
            if ($row['type'] == self::CONFIG_TYPE_ENUM) {
                $configArr = explode(",",$row['extra']);
                $configValue = [];
                foreach ($configArr as $arr) {
                    $splitArr = explode(":",$arr);
                    $configValue[$splitArr[0]] = $splitArr[1];
                }
                $row['options'] = $configValue;
            } else {
                $row['options'] = [];
            }
            if (!isset($configItemsArr[$row['group']])) {
                $configItemsArr[$row['group']] = [];
            }
            array_push($configItemsArr[$row['group']], $row);
        }
        return $configItemsArr;
    }
    
    public function beforeDoCreate($params) {
        if ($params['type'] == self::CONFIG_TYPE_ARRAY && !empty($params['extra'])) {
            $params['value'] = $params['extra'];
            $params['extra'] = "";
        } elseif ($params['type'] != self::CONFIG_TYPE_ARRAY && $params['type'] != self::CONFIG_TYPE_ENUM && !empty($params['extra'])) {
            $params['extra'] = "";
        }
        return $params;
    }
    
    public function beforeDoUpdate($params) {
        return $this->beforeDoCreate($params);
    }
    
    public function afterCreate($params) {
        if ($params['type'] == self::CONFIG_TYPE_ARRAY) {
            Cache::rm('configItem');
        }
    }
    
    public function afterDoUpdate($params) {
        if ($params['type'] == self::CONFIG_TYPE_ARRAY) {
            Cache::rm('configItem');
        }
    }
    
    public function afterDoDelete($params) {
        if ($params['type'] == self::CONFIG_TYPE_ARRAY) {
            Cache::rm('configItem');
        }
    }
}
