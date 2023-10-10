<?php

/**
 * Description of Menu
 *
 * @author ocean
 */
namespace app\admin\validate;
use think\Validate;

class Menu extends Validate {
    
    protected $rule =   [
        'pid'  => 'require',
        'text'   => 'require',
        'controller' => 'require',
        'method' => 'require',
    ];
    
    protected $message  =   [
        'pid.require' => '请选择上级菜单',
        'text.require'     => '请输入功能名称',
        'controller.require'   => '请输入控制器名',
        'method.require'  => '请输入方法名',  
    ];
    
    protected $scene = [
        
    ];
}
