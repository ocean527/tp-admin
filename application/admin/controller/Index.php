<?php
namespace app\admin\controller;

use think\Controller;
use app\common\controller\AdminBase;
use think\Db;
use app\common\model\Parking;
use app\common\model\ParkingRecord;

class Index extends AdminBase
{
    public function index() {
        return $this->fetch();
    }
    
    public function dashboard() {
        //当日异常数量
        $todayExceptionCount = Db::name("console_message")
                                ->where("create_time", ">=", strtotime(date("Y-m-d 00:00:00")))
                                ->where("type",2)
                                ->count();
        $consoleOnlineCount = Db::name("console")->where("last_online_time",">=", date("Y-m-d H:i:s", strtotime("-70 SECOND")))
                                ->count();
        
        $consoleCount = Db::name("console")->count();
        //终端在线率
        $consoleOnlineRate = $consoleCount == 0 ? "0%" : (round($consoleOnlineCount/$consoleCount, 2) * 100) . "%";
        
        $mqttOnlineCount = Db::name("console")->where("mqtt_status",1)
                                ->count();
        //mqtt在线率
        $mqttOnlineRate = $consoleCount == 0 ? "0%" : (round($mqttOnlineCount/$consoleCount, 2) * 100) . "%";
        
        //当日验证码请求数量
        $todayCodeRequestCount = Db::name("ota_sms_code")
                                ->where("create_time", ">=", strtotime(date("Y-m-d 00:00:00")))
                                ->count();
        
        $near15DaysArr = [];
        $exceptionArr = [];
        for ($i = 14; $i >= 0; $i--) {
            $tmpDate = date("Y-m-d", strtotime("-{$i} day"));
            $near15DaysArr[] = $tmpDate;
            $exceptionArr[] = Db::name("console_message")
                            ->where("type", 2)
                            ->where("occur_time",">=",$tmpDate . " 00:00:00.000")
                            ->where("occur_time","<=",$tmpDate . " 23:59:59.000")
                            ->count();
        }
        
        $this->assign("todayExceptionCount", $todayExceptionCount);
        $this->assign("consoleOnlineRate", $consoleOnlineRate);
        $this->assign("mqttOnlineRate", $mqttOnlineRate);
        $this->assign("todayCodeRequestCount", $todayCodeRequestCount);
        $this->assign("consoleOnlineCount", $consoleOnlineCount);
        $this->assign("mqttOnlineCount", $mqttOnlineCount);
        $this->assign("near15DaysArr", json_encode($near15DaysArr));
        $this->assign("exceptionArr", json_encode($exceptionArr));
        return $this->fetch();
    }
    
    public function profile() {
        $user = session("userinfo");
        $role = Db::name("role")->find($user['role_id']);
        $user['role_name'] = $role ? $role['text'] : "超级管理员";
        unset($user['menu']);
        $this->assign($user);
        $this->assign("userJson", json_encode($user));
        return $this->fetch();
    }
}
