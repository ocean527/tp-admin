<?php

/**
 * Description of Menu
 *
 * @author ocean
 */

namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;
use app\common\model\LayUI;
use app\common\model\Admin;

class Menu extends AdminBase {
    
    public function treeSelectOptions() {
        $data = Db::name("menu")->field("id,text as name,pid")->where("display",1)->select();
        $data = tree_array($data);
        array_unshift($data, [
            'id' => 0,
            'name' => '作为顶级',
            'checked' => false,
            'open' => true,
            'pid' => 0
        ]);
        LayUI::selectOptions($data);
    }
    
    public function xtreeSelectOptions() {
        $data = Db::name("menu")->field("id,text,id as value,text as title,pid")->select();
        $data = tree_array($data,0,'data');
        LayUI::selectOptions($data);
    }
    
    public function menuJson() {
        $userinfo = session("userinfo");
        $adminModel = new Admin();
        $userMenus = $adminModel->getUserMenu($userinfo['user_id']);
        $menu = [];
        foreach ($userMenus as $key => $item) {
            $menu[$key]["id"] = $item["id"];
            $menu[$key]["pid"] = $item["pid"];
            $menu[$key]["icon"] = $item["icon"];
            $menu[$key]["title"] = $item["text"];
            $menu[$key]["path"] = $item['method'] == "*" ? "" : "#" . url($item['controller'].'/'.$item['method']);
        }
        array_unshift($menu, [
            "pid" => 0,
            "id" => -1,
            "icon" => "&#xe66a;",
            "title" => "概况",
            "path" => "#/admin/index/dashboard.html"
        ]);
        return json(tree_array($menu));
    }
}
