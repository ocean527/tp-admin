<?php

/**
 * Description of Role
 *
 * @author ocean
 */

namespace app\admin\validate;

use think\Validate;

class Role extends Validate {
    
    protected $rule =   [
        'text'   => 'require',
        'menu_id' => 'require'
    ];
    
    protected $message  =   [
        'text.require'     => '请输入角色名称',
        'menu_id.require'  => '请分配权限'
    ];
}
