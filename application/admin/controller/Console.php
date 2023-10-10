<?php
namespace app\admin\controller;

use think\Controller;
use app\common\controller\AdminBase;
use think\Db;

class Console extends AdminBase
{
    public function sync() {
        $syncTime = date("Y-m-d H:i:s");
        $rows = Db::connect('hotel_db_config')
        ->table('hotel_device')
        ->field("device_id as id,hotel_id,device_name as name,is_online as mqtt_status,device_identity as mac,'{$syncTime}' as sync_time")
        ->where([
            'is_delete' => 0,
            'device_type' => 'terminal'
        ])->select();
        $existsRowsMac = Db::name("console")->column("mac");
        foreach ($rows as $item) {
            if (in_array($item['mac'], $existsRowsMac)) {
                Db::name("console")->where([
                    "mac" => $item['mac']
                ])->update([
                    "name" => $item["name"],
                    "hotel_id" =>  $item["hotel_id"],
                    "mqtt_status" => $item["mqtt_status"],
                    "sync_time" => $item["sync_time"]
                ]);
            } else {
                Db::name("console")->insert([
                    "mac" => $item['mac'],
                    "name" => $item["name"],
                    "hotel_id" =>  $item["hotel_id"],
                    "mqtt_status" => $item["mqtt_status"],
                    "sync_time" => $item["sync_time"]
                ]);
            }
        }
        $this->success("同步成功");
    }
}
