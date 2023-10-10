<?php

/**
 * Description of File
 *
 * @author ocean
 */

namespace app\common\util;

class FtpFile {
    
    public static function writeFtpData($str, $parkId, $type) {
        $date = date("Y-m-d");
        $dirName = \Env::get('root_path') . 'ftpdata' . DIRECTORY_SEPARATOR . $parkId . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR;
        if (!is_dir($dirName)) {
            mkdir($dirName,0777,true);
        }
        $filename = $dirName . self::mircoTimestamp() . ".json";
        return false !== file_put_contents($filename, $str);
    }
    
    public static function mircoTimestamp() {
        return str_replace(".", "", microtime(true));
    }
}
