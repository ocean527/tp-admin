<?php

/**
 * Description of Mqtt
 *
 * @author ocean
 */

namespace app\common\util;

class Mqtt {
    
    const QOS_AT_MOST_ONCE = 0;
    const QOS_AT_LEAST_ONCE = 1;
    const QOS_EXACT_ONCE = 2;
    
    public static function publish($topic, $body, $qos = self::QOS_AT_MOST_ONCE) {
        $clientID = 'ge_parking_pub_' . time();
        $c = new \Mosquitto\Client($clientID);
        $mid = 0;
        $c->onConnect(function($code, $message) use ($c, $topic, $body, $qos, &$mid) {
            $mid = $c->publish($topic, $body, $qos);
        });
        
        $c->onPublish(function($publishedId) use ($c, &$mid){
            if ($publishedId == $mid) {
                $c->disconnect();
            }
        });
        
        $c->setCredentials(config("mqtt.user"), config("mqtt.pwd"));
        $c->connect(config("mqtt.server"), config("mqtt.port"), config("mqtt.keep_alive"));
        $c->loopForever();
    }
    
    public static function subscribe($topics, $qos = self::QOS_AT_MOST_ONCE , $lastWillMsg = []) {
        $clientID = 'hotel_message_center';
        $c = new \Mosquitto\Client($clientID);
        $c->onConnect(function($code, $message) use ($c,$topics,$qos) {
            foreach ($topics as $topic => $e){
                $msgQos = $e['qos']??$qos;
                $c->subscribe($topic, $msgQos);
            }
        });
        $c->onMessage(function($message) use ($topics,$qos) {
            $topic = $message->topic;
            $payload = $message->payload;
            if(isset($topics[$topic]) && $e = $topics[$topic]){
                $fun = $e['function'];
                if(is_callable($fun)){
                    call_user_func($fun,$topic,$payload);
                }
            }
        });
        if($lastWillMsg){
            $topic = $lastWillMsg['topic']??'';
            $payload = $lastWillMsg['payload']??'';
            $qos = $lastWillMsg['qos']??self::QOS_AT_MOST_ONCE;
            $retain = boolval($lastWillMsg['retain']);
            if(!empty($topic) && !empty($payload)){
                $c->setWill($topic,$payload,$qos,$retain);
            }
        }
        
        $timeout = config("?mqtt.timeout") ? config("mqtt.timeout") : 1000;
        $c->setCredentials(config("mqtt.user"), config("mqtt.pwd"));
        $c->connect(config("mqtt.server"), config("mqtt.port"), config("mqtt.keep_alive"));
        $c->loopForever($timeout);
    }
}
