<?php

/**
 * Description of Error
 *
 * @author ocean
 */

namespace app\admin\controller;
use think\Request;
use app\common\controller\AdminBase;
use think\exception\TemplateNotFoundException;
use think\exception\ClassNotFoundException;
use Exception;
use think\Db;
use app\common\model\LayUI;
use app\common\model\AdminCommon;
use app\common\model\Menu;

class Error extends AdminBase {
    
    public function _empty(Request $request) {
        try {
            $controller = $request->controller();
            $action = $request->action();
            if ($request->isGet()) { 
                $route = "{$controller}/{$action}";
                $this->assign("route",$route);
                $this->assign("controller", $controller);
                if ($action == "create" || $action == "index") {
                    try {
                        $pk = Db::name(uncamelize($controller))->getPk();
                        $this->assign("pk",$pk);
                    } catch (Exception $ex) {}
                }
                return $this->fetch(strtolower($route));
            } else {
                $params = $request->post();
                $modelName = "\\app\\common\\model\\" . ucfirst($controller);
                $modelAction = "do" . ucfirst($action);
                if (class_exists($modelName) && method_exists($operateModel = new $modelName, $modelAction)) {
                    return $operateModel->$modelAction($params);
                } elseif (method_exists($operateModel = new AdminCommon(), $modelAction)) {
                    return $operateModel->setTable($controller)->$modelAction($params);
                } else {
                    return $this->$action($request);
                }
            }
        } catch (TemplateNotFoundException $tempEx) {
            $this->error("tpl not found");
        } catch (ClassNotFoundException $classEx) {
            $this->error("api route not found");
        }
    }
    
    protected function selectOptions($request) {
        $controller = $request->controller();
        $data = Db::name($controller)->select();
        LayUI::selectOptions($data);
    }
}
