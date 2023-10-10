<?php

/**
 * Description of Article
 *
 * @author ocean
 */

namespace app\common\model;
use think\Model;
use app\common\model\AdminCommonTrait;

class AdminLog extends Model {
    use AdminCommonTrait;
    
    public function paramPreprocess($query, $params) {
        $query->alias("al")
            ->field("al.*,a.user_name,a.real_name")
            ->join("admin a","al.user_id = a.user_id","left");
    }
}
