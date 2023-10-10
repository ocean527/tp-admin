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
use app\common\model\Config as ConfigModel;

class Config extends AdminBase {
    
    public function setting() {
        $tabArr = Config("extra.CONFIG_GROUP_LIST");
        $configModel = new ConfigModel();
        $configItems = $configModel->getConfigItems();
        $this->assign("tabArr", $tabArr);
        $this->assign("configItems", $configItems);
        return $this->fetch();
    }
    
    public function updateSetting() {
        $params = $this->request->post();
        Db::startTrans();
        $result = false;
        try {
            foreach ($params as $key => $val) {
                Db::name("config")->where("name",$key)->setField('value', $val);
            }
            Db::commit();
            $result = true;
        } catch (\Exception $e) {
            Db::rollback();
        }
        if ($result) {
            $this->success("保存成功");
        } else {
            $this->error("保存失败");
        }
    }
    
    public function groupOptions() {
        $data = [];
        foreach (ConfigModel::$groupArr as $key => $val) {
            $data[] = [
                'id' => $key,
                'text' => $val
            ];
        }
        
        LayUI::selectOptions($data);
    }
    
    public function typeOptions() {
        $data = [];
        foreach (ConfigModel::$typeArr as $key => $val) {
            $data[] = [
                'id' => $key,
                'text' => $val
            ];
        }
        
        LayUI::selectOptions($data);
    }
}
