<?php

/**
 * Description of Index
 *
 * @author ocean
 */

namespace app\api\controller;
use think\Controller;
use \Exception;
use think\Db;
use app\api\model\InvokeError;
use think\Response;

class Invoke extends Controller {
    
    const REQUEST_TIME_OUT = 300;   //单位：秒
    
    public function index() {
        $data = $this->request->post();
        $respondData = [
            'code' => 0,
            'data' => [],
            'msg' => 'success'
        ];
        try {
            if (empty($data)) {
                throw new Exception("request error");
            }
            if (empty($data['app_id'])) {
                throw new Exception("app id missing");
            }
            $app = Db::name("app")->where([
                'app_id' => $data['app_id'],
                'locked' => 0
            ])->find();
            if (!$app) {
                throw new Exception("app id error");
            }
            if (empty($data['timestamp'])) {
                throw new Exception("timestamp missing");
            }
            if (time() - $data['timestamp'] >= self::REQUEST_TIME_OUT) {
                throw new Exception("request timeout");
            }
            if (empty($data['method'])) {
                throw new Exception("method missing");
            }
            if (strpos($data['method'], ".") === false) {
                throw new Exception("method error");
            }
            if (empty($data['sign'])) {
                throw new Exception("sign missing");
            }
            if ($data['sign'] != $this->generateSign($data, $app['app_secret'])) {
                throw new Exception("sign error");
            }
            if (!empty($data['param'])) {
                $param = json_decode($data['param'], true);
                if (is_null($param)) {
                    throw new Exception("param error");
                }
            } else {
                $param = [];
            }
            $method = str_replace(".", "/", $data['method']);
            $respondData['data'] = action($method, ['param' => $param], "action");
            return json($respondData);
        } catch (Exception $e) {
            $respondData['code'] = InvokeError::getErrorCode($e->getMessage());
            $respondData['msg'] = $e->getMessage();
            return json($respondData);
        }
    }
    
    protected function generateSign($data, $appSecret) {
        unset($data['sign']);
        ksort($data);
        $dataStr = http_build_query($data);
        return md5(md5($dataStr . $appSecret));
    }
}
