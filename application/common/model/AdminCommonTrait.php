<?php

/**
 * Description of AdminModelBase
 *
 * @author ocean
 */

namespace app\common\model;

use think\Db;
use app\common\model\LayUI;
use think\Exception;
use think\Model;
use traits\controller\Jump;

trait AdminCommonTrait {
    use Jump;
    
    protected $name;   //表名
    
    public function setTable($name) {
        $this->name = $name;
        return $this;
    }
    
    public function getTableName() {
        return uncamelize($this->name);
    }
    
    public function doIndex($params) {
        $page = empty($params['page']) ? 0 : intval($params['page']);
        $limit = empty($params['limit']) ? 0 : intval($params['limit']);
        $orderBy = empty($params['orderby']) ? '' : $params['orderby'];
        $order = empty($params['order']) ? '' : $params['order'];
        
        $query = Db::name($this->getTableName());
        $fields = Db::getTableFields($this->getTableName());
        if (in_array("create_time", $fields) && substr(Db::getFieldsType($this->getTableName(), 'create_time'),0,3) == "int") {
            $query->withAttr('create_time', function($value, $data) {
                return date("Y-m-d H:i:s", $value);
            });
        }
        if (in_array("update_time", $fields) && substr(Db::getFieldsType($this->getTableName(), 'update_time'),0,3) == "int") {
            $query->withAttr('update_time', function($value, $data) {
                return date("Y-m-d H:i:s", $value);
            });
        }
        if (method_exists($this, "paramPreprocess")) {
            $this->paramPreprocess($query, $params);
        }
        $count = $query->count();
        if (!empty($orderBy) && !empty($order)) {
            $query->order($orderBy,$order);
        }
        if ($limit && $page) {
            $query->limit($limit)->page($page);
        }
        $data = $query->select();
        if (method_exists($this, "dataFormatter")) {
            $this->dataFormatter($query, $data);
        }
        return LayUI::table($count, $data);
    }
    
    public function doTree($params) {
        if (isset($params['page'])) {
            unset($params['page']);
        }
        if (isset($params['limit'])) {
            unset($params['limit']);
        }
        $this->doIndex($params);
    }
    
    protected function checkData($params,$scene = '') {
        $validateClassName = "\\app\\admin\\validate\\".ucfirst($this->name);
        if (class_exists($validateClassName)) {
            $validate = new $validateClassName;
            if ($scene) {
                $validate->scene($scene);
            }
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }
        }
    }
    
    protected function getModelInstance() {
        $modelName = "\\app\\common\\model\\" . ucfirst($this->name);
        return class_exists($modelName) ? new $modelName() : null;
    }
    
    public function doCreate($params) {
        $this->checkData($params);
        $query = Db::name($this->getTableName());
        $model = $this->getModelInstance();
        if (is_null($model)) {
            $fields = Db::getTableFields($this->getTableName());
            if (in_array("create_time", $fields) && substr(Db::getFieldsType($this->getTableName(), 'update_time'),0,3) == "int") {
                $params['create_time'] = time();
            }
            if (in_array("update_time", $fields) && substr(Db::getFieldsType($this->getTableName(), 'update_time'),0,3) == "int") {
                $params['update_time'] = time();
            }
            $result = $query->data($params)->insert();
        } else {
            if (method_exists($model, "beforeDoCreate")) {
                $params = $model->beforeDoCreate($params);
            }
            $pk = $query->getPk();
            unset($params[$pk]);
            $result = $model->data($params)->save();
            $params['insert_id'] = $model->$pk;
            if ($result && method_exists($model, "afterDoCreate")) {
                $params = $model->afterDoCreate($params);
            }
        }
        if ($result) {
            $this->success("添加成功");
        } else {
            $this->error("添加失败");
        }
    }
    
    public function doUpdate($params) {
        $query = Db::name($this->getTableName());
        $pk = $query->getPk();
        if (empty($params[$pk])) {
            $this->error("缺少参数");
        }
        $this->checkData($params,'edit');
        $model = $this->getModelInstance();
        if (is_null($model)) {
            $fields = Db::getTableFields($this->getTableName());
            if (in_array("update_time", $fields) && substr(Db::getFieldsType($this->getTableName(), 'update_time'),0,3) == "int") {
                $params['update_time'] = time();
            }
            $result = $query->data($params)->update();
        } else {
            if (method_exists($model, "beforeDoUpdate")) {
                $params = $model->beforeDoUpdate($params);
            }
            $result = $model->isUpdate(true)->save($params);
            if ($result && method_exists($model, "afterDoUpdate")) {
                $params = $model->afterDoUpdate($params);
            }
        }
        if ($result) {
            $this->success("更新成功");
        } else {
            $this->error("更新失败");
        }
    }
    
    public function doDelete($params) {
        $query = Db::name($this->getTableName());
        $pk = $query->getPk();
        if (empty($params[$pk])) {
            $this->error("缺少参数");
        }
        if (strpos(",",$params[$pk]) === false) {
            $idArr = explode(",", $params[$pk]);
        } else {
            $idArr = (array)$params[$pk];
        }
        $result = $query->delete($params[$pk]);
        $model = $this->getModelInstance();
        if ($model && $result && method_exists($model, "afterDoDelete")) {
            $params = $model->afterDoDelete($params);
        }
        if ($result) {
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }

    public function doRow($params){
        $query = Db::name($this->getTableName());
        $model = $this->getModelInstance();
        $pk = $query->getPk();
        $id = $params[$pk]??0;
        if (method_exists($model, "getRow")) {
            $row = $model->getRow($id);
        }else{
            $query->where($pk,$params[$pk]);
            $row = $query->find();
        }
        LayUI::selectOptions($row);
     }
}
