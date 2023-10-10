<?php

/**
 * Description of Admin
 *
 * @author ocean
 */

namespace app\admin\validate;
use think\Validate;
use think\db;

class Admin extends Validate {
    
    protected $rule =   [
        'user_name'  => 'require|unique:admin',
        'real_name'   => 'require',
        'password' => 'requireCallback:check_password_require',
        'sex' => 'require',
        'role_id' => 'requireCallback:check_role_id_require',
    ];
    
    protected $message  =   [
        'user_name.require' => '请请输入用户名',
        'user_name.unique' => '用户名已经存在',
        'real_name.require'     => '请输入真实姓名',
        'password.requireCallback'   => '请输入密码',
        'sex.require'  => '请选择性别',
        'role_id.requireCallback' => '请选择角色'
    ];
    
    protected $scene = [
        'edit' => ['user_name','real_name','password','sex','role_id']
    ];
    
    public function check_role_id_require($value, $data) {
        //超管不需要选角色
        if ($data['user_id']) {
            $admin = Db::name("admin")->find($data['user_id']);
            return $admin['is_open'] == 1 ? false : true;
        }
        return true;
    }
    
    public function check_password_require($value, $data) {
        //新创建必须填写密码，更新时选填
        return empty($data['user_id']) ? true : false;
    }
}
