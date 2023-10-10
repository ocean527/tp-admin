<?php

/**
 * Description of Common
 *
 * @author ocean
 */

namespace app\admin\controller;
use app\common\controller\AdminBase;
use traits\controller\Jump;
use think\Db;
use app\common\util\Mqtt;

class Common extends AdminBase {
    use Jump;
    
    public function roomStatus() {
        $ids = $this->request->post("ids");
        $operate = $this->request->post("operate");
        if (empty($ids) || !in_array($operate,[0,1])) {
            $this->error("参数错误");
        }
        $roomTypeNames = Db::name("room_type")->where("id","in",$ids)->column("name");
        if (empty($roomTypeNames)) {
            $this->error("数据错误");
        }
        Mqtt::publish("geaii/room_status/demo", json_encode([
            "room_type_name" => implode(",", $roomTypeNames),
            "operate" => $operate == 0 ? "N" : "G"
        ]), Mqtt::QOS_EXACT_ONCE);
        Db::name("room_type")->where("id","in",$ids)->update(['status' => $operate]);
                
        $this->success("操作成功");
    }
}
