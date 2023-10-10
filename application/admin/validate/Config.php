<?php

/**
 * Description of Admin
 *
 * @author ocean
 */

namespace app\admin\validate;
use think\Validate;
use think\db;
use app\common\model\Config as ConfigModel;

class Config extends Validate {
    
    protected $rule =   [
        'name'  => 'require|unique:config|regex:^[A-Z_]+$',
        'title'   => 'require',
        'group' => 'require',
        'type' => 'require',
        'extra' => 'requireCallback:extra_require_check|extra_format_check'
    ];
    
    protected $message  =   [
        'name.require' => '请请输入配置标识',
        'name.unique' => '配置标识已经存在',
        'name.regex' => '标识格式不正确',
        'title.require'     => '请输入配置标题',
        'group.require'     => '请选择分组',
        'type.require'     => '请选择类型',
        'extra.requireCallback' => '请填写配置值',
        'extra.extra_format_check' => '配置值格式错误',
    ];
    
    protected $scene = [
        
    ];
    
    public function extra_require_check($value, $data) {
        if ($data['type'] == ConfigModel::CONFIG_TYPE_ARRAY || $data['type'] == ConfigModel::CONFIG_TYPE_ENUM) {
            return true;
        }
        return false;
    }
    
    public function extra_format_check($value, $data) {
        $arr = explode(",",$value);
        return count($arr) === preg_match_all("/[\w\x{4e00}-\x{9fa5}]+\:[\w\x{4e00}-\x{9fa5}]+/u",$value);
    }
}
