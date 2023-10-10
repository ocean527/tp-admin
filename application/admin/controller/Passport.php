<?php

/**
 * Description of Passport
 *
 * @author ocean
 */

namespace app\admin\controller;

use think\Controller;
use app\common\model\Admin;
use think\Db;

class Passport extends Controller {
    
    public function login() {
        if ($this->request->isPost()) {
            $username = $this->request->post("username","");
            $password = $this->request->post("password","");
            $isRememberMe = $this->request->post("rememberMe","");
            
            if(empty($username) || !preg_match("/^[a-zA-Z0-9_]+$/", $username)){
                $this->error("用户名不合法");
            }
            if(empty($password)){
                $this->error("密码不能为空");
            }
            $user = Admin::getByUserName($username);
            if (empty($user)) {
                $this->error("用户名不存在");
            }
            if($user['locked']){
                $this->error("该用户已被锁定");
            }
            if ($isRememberMe === "true") {
                if (empty($_COOKIE['r_u']) || empty($_COOKIE['r_p'])) {
                    //第一次选择记住,保存到cookie
                    if($user['password'] != password_md5($password)){
                        $this->error("密码错误");
                    }
                    setcookie("remember", 1, time() + 3600 * 24 * 7, "/");
                    setcookie('r_u', $username, time() + 3600 * 24 * 7, "/");
                    setcookie('r_p', md5($username. $user['password'].$_SERVER['REMOTE_ADDR'].config('crypt_key')),time() + 3600 * 24 * 7, "/");
                } else {
                    //之前已经记住过，自动登录
                    if($user['password'] != password_md5($password) 
                            && md5($username.$user['password'].$_SERVER['REMOTE_ADDR'].config('crypt_key')) != $_COOKIE['r_p']){
                        setcookie("remember", "", time() - 3600);
                        setcookie("r_u", "", time() - 3600);
                        setcookie("r_p", "", time() - 3600);
                        $this->error("密码错误");
                    }
                }
            } else {
                if($user['password'] != password_md5($password)){
                    $this->error("密码错误");
                }
                setcookie("remember", "", time() - 3600, "/");
                setcookie("r_u", "", time() - 3600, "/");
                setcookie("r_p", "", time() - 3600, "/");
            }
            $adminModel = new Admin();
            //$user['menu'] = $adminModel->getUserMenu($user['user_id']);
            $user['privilege'] = $adminModel->getUserPrivilege($user['user_id']);
            unset($user['password']);
            $updateData = [
                'last_login_ip' => $user['now_login_ip'],
                'now_login_ip' => $this->request->server("REMOTE_ADDR"),
                'last_login_time' => strtotime($user['now_login_time']),
                'now_login_time' => time()
            ];
            $user->save($updateData);
            Db::name("admin_log")->insert([
                'user_id' => $user['user_id'],
                'note' => "用户登录",
                'params' => json_encode($updateData),
                'create_time' => time()
            ]);
            session("userinfo", $user->toArray());
            $this->success("登录成功", url("index/index"));
        } else {
            return $this->fetch();
        }
    }
    
    public function logout() {
        session("userinfo", null);
        setcookie("remember", "", time() - 3600, "/");
        setcookie("r_u", "", time() - 3600, "/");
        setcookie("r_p", "", time() - 3600, "/");
        return redirect(url("passport/login"));
    }
}
