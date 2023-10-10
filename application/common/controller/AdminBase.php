<?php

/**
 * Description of Adminbase
 *
 * @author ocean
 */

namespace app\common\controller;

use think\Controller;
use app\admin\controller\Error;
use think\Request;
use app\common\model\Config;
use app\common\model\Menu;
use think\Db;

abstract class AdminBase extends Controller {
    
    protected $middleware = ['Auth'];
    public static $isEmptyAction = false;
    
    public function __construct(\think\App $app = null) {
        parent::__construct($app);
        (new Config())->loadConfig();
        $userinfo = session("userinfo");
        $this->_writeLog();
    }
    
    public function _empty(Request $request) {
        AdminBase::$isEmptyAction = true;
        return (new Error())->_empty($request);
    }
    
    private function _writeLog() {
        if (!AdminBase::$isEmptyAction && $this->request->isGet()) {  //防止找不到方法时，Error对象会再次调用构造函数，导致重复写日志
            $controller = strtolower($this->request->controller());
            $action = strtolower($this->request->action());
            $userinfo = session("userinfo");
            if (($action == "index" || $action == "tree") && isset($userinfo['privilege'][$controller . "-" . $action])) {
                Db::name("admin_log")->insert([
                    'user_id' => $userinfo['user_id'],
                    'note' => "查看 " . $userinfo['privilege'][$controller . "-" . $action]["navigate_name"],
                    'params' => "",
                    'create_time' => time()
                ]);
            }
        }
    }
}
