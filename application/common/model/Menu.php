<?php

/**
 * Description of Menu
 *
 * @author ocean
 */

namespace app\common\model;
use think\Model;
use think\Db;
use think\Exception;
use app\common\model\AdminCommonTrait;

class Menu extends Model {
    use AdminCommonTrait;
    
    protected $pk = 'id';
    
    public function getRouteAttr($value, $data) {
        return $data['controller'] . "/" . $data['method'];
    }
    
    public function paramPreprocess($query, $params) {
        $query->withAttr('route', function($value, $data) {
            return $data['controller'] . "/" . $data['method'];
        });
    }
    
    public function getMenuByRole($roleId, $isPrivilege = false) {
        $where = [];
        if ($roleId) {
            $menuIds = Db::name("role")->where(['id' => $roleId])->find();
            if (empty($menuIds)) {
                throw new Exception("获取角色菜单失败");
            }
            $allMenuIds = $this->getAllMenuId($menuIds['menu_id']);
            $where['id'] = $allMenuIds;
        }
        if (!$isPrivilege) {
            $where["display"] = 1;
        }
        return $this->field("id,text,pid,controller,method,display,orderby,icon")
                    ->where($where)
                    ->order("orderby asc")
                    ->select();
    }
    
    public function getPrivilegeByRole($roleId) {
        $menuArr = $this->getMenuByRole($roleId, true);
        $privilege = [];
        foreach ($menuArr as $row) {
            $privilege[strtolower($row['controller']) . "-" . strtolower($row['method'])] = [
                "id" => $row['id'],
                'pid' => $row['pid'],
                "menu_name" => $row['text'],
                "navigate_name" => $this->retrieveNavigateuName($menuArr, $row['id'])
            ];
        }
        return $privilege;
    }
    
    public function getNavigateName($menuArr, $controller, $action) {
        if (empty($menuArr) || empty($controller) || empty($action)) {
            return "";
        }
        $find = [];
        foreach ($menuArr as $row) {
            if ($row['controller'] == $controller && $row['method'] == $action) {
                $find = $row;
                break;
            }
        }
        if (empty($find)) {
            return "";
        }
        return $this->retrieveNavigateuName($menuArr, $find['id']);
    }
    
    public function retrieveNavigateuName($menuArr, $id, &$navigateArr = []) {
        foreach ($menuArr as $row) {  
            if ($row['id'] == $id) {
                if ($row['pid'] != 0) { 
                    $this->retrieveNavigateuName($menuArr, $row['pid'], $navigateArr);
                }
                $navigateArr[] = $row['text'];
                break;
            }
        }
        return $navigateArr ? implode("-", $navigateArr) : "";
    }
    
    public function getAllMenuId($menuIds) {
        if (is_string($menuIds)) {
            $menuIds = strpos($menuIds,",") === false ? (array)$menuIds : explode(",", $menuIds);
        }
        $menuArr = $this->order("orderby asc")->select()->toArray();
        $idArr = [];
        foreach ($menuIds as $mid) {
            $idArr = array_merge($idArr, $this->childFindParent($menuArr, $mid));
        }
        $idArr = array_unique($idArr);
        sort($idArr);
        return $idArr;
    }
    
    public function childFindParent($menuArr, $childId) {
        $idArr = [];
        while (true) {
            foreach ($menuArr as $row) {
                if ($row['id'] == $childId) {
                    $idArr[] = $childId;
                    if ($row['pid'] != 0) { 
                        $childId = $row['pid'];
                        break;
                    } else {
                        $idArr[] = 0;
                        return $idArr;
                    }
                }
            }
        }
    }
}
