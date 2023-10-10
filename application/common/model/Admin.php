<?php

/**
 * Description of Admin
 *
 * @author ocean
 */

namespace app\common\model;

use think\Model;
use app\common\model\Menu;
use app\common\model\AdminCommonTrait;

class Admin extends Model {
    use AdminCommonTrait;
    
    protected $pk = 'user_id';
    protected $insert = ['password'];
    
    public function paramPreprocess($query, $params) {
        $query->withAttr('now_login_time', function($value, $data) {
            return date("Y-m-d H:i:s", $value);
        });
        $query->alias("a")
            ->field("a.*,r.text as role_name")
            ->join("role r","a.role_id = r.id","left");
    }
    
    public function beforeDoUpdate($data) {
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }
    
    public function afterDoUpdate($data) {
        $user = session("userinfo");
        if ($data['user_id'] == $user['user_id']) {
            $updateData = array_merge($user, $data);
            session("userinfo",$updateData);
        }
    }
    
    public function setPasswordAttr($value) {
        return password_md5($value);
    }
    
    public function getNowLoginTimeAttr($value) {
        return date("Y-m-d H:i:s", $value);
    }
    
    public function getLastLoginTimeAttr($value) {
        return date("Y-m-d H:i:s", $value);
    }
    
    public function getUserMenu($userId) {
        $user = $this->get($userId);
        $menuModel = new Menu();
        return $menuModel->getMenuByRole($user->role_id);
    }
    
    public function getUserPrivilege($userId) {
        $user = $this->get($userId);
        $menuModel = new Menu();
        return $menuModel->getPrivilegeByRole($user->role_id);
    }
}
