<?php

/**
 * Description of Article
 *
 * @author ocean
 */

namespace app\common\model;
use think\Model;
use app\common\model\AdminCommonTrait;

class Role extends Model {
    use AdminCommonTrait;
    
    protected $pk = 'id';
    
    public function paramPreprocess($query, $params) {

    }
    
    public function afterDoCreate() {
        
    }
}
