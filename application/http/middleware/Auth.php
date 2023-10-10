<?php
/*
 * 鉴权中间件
 */

namespace app\http\middleware;

class Auth
{
    use \traits\controller\Jump;
    
    /**
     * 通用路由
     * @var type 
     */
    private $allowRoute = [
        "index/index",
        "index/profile",
        "menu/menujson",
    ];
    
    /**
     * 通用方法
     * @var type 
     */
    private $allowAction = [
        'selectoptions',
        'treeselectoptions',
        'dashboard',
        'xtreeselectoptions'
    ];
    
    public function handle($request, \Closure $next)
    {
        if (!$this->authLogin()) {
            return redirect(url("admin/passport/login"));
        }
        if (!$this->authAccessPri($request->controller(),$request->action())) {
            $this->error("对不起，您没有权限访问");
        }
        return $next($request);
    }
    
    protected function authLogin() {
        return session("?userinfo");
    }
    
    protected function authAccessPri($controller, $action) {
        $controller = strtolower(uncamelize($controller));
        $action = strtolower(uncamelize($action));
        $route = $controller . "/". $action;
        $userInfo = session("userinfo");
        if ($userInfo['is_open'] == 1) {
            return true;
        }
        if (in_array($route, $this->allowRoute)) {
            return true;
        }
        if (in_array($action, $this->allowAction)) {
            return true;
        }
        if (!empty($userInfo['privilege'])) {
            $privileges = array_keys($userInfo['privilege']);
            return in_array($controller . "-" . $action, $privileges);
        }
        return false;
    }
    
    protected function authDataPri() {
        
    }
}
