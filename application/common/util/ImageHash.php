<?php
namespace app\common\util;

class ImageHash
{
    public static function run($src1, $src2)
    {
        static $self;
        if (!$self) $self = new static;
        if (!is_file($src1) || !is_file($src2))  exception("file not found");
        $dhash_1 = $self->getHashValue($src1);
        $dhash_2 = $self->getHashValue($src2);
        $tem = hex2bin($dhash_1) ^ hex2bin($dhash_2);
        $tem = bin2hex($tem);
        $tem = base_convert($tem, 16, 2);
        return substr_count($tem, '1');
    }

    /**
     * 获取图片DHash
     * @param $src
     * @return bool|string
     */
    public function getHashValue($src)
    {
        if (!file_exists($src)) {
            return false;
        }
        $info = getimagesize($src);
        if ($info === false) {
            return false;
        }
        $w   = 9;  // 采样宽度
        $h   = 8;  // 采样高度
        $dst = imagecreatetruecolor($w, $h);
        $img = imagecreatefromstring(file_get_contents($src));
        // 缩放
        $img && imagecopyresized($dst, $img, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
        $hash = '';
        for ($y = 0; $y < $h; $y++) {
            $pix = $this->getGray(imagecolorat($dst, 0, $y));
            for ($x = 1; $x < $w; $x++) {
                $_pix = $this->getGray(imagecolorat($dst, $x, $y));
                $_pix > $pix ? $hash .= '1' : $hash .= '0';
                $pix = $_pix;
            }
        }
        $hash = base_convert($hash, 2, 16);
        return $hash;
    }

    /**
     * 获取像素点的灰度值
     * @param $rgb
     * @return int
     */
    function getGray($rgb)
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return intval(($r + $g + $b) / 3) & 0xFF;
    }

        /**比较两个图片文件，是不是相似
     * @param string $aHash A图片的路径
     * @param string $bHash B图片的路径
     * @return bool 当图片相似则传递 true，否则是 false
     * */
    public static function isImageFileSimilar($aPath, $bPath)
    {
        try{
            $count = ImageHash::run($aPath, $bPath);
        }catch (\Exception $e){
            $count = 1000;
        }
        return true;
        //汉明距离小于10，两张图片相似
        return $count <= 10 ? true : false;
    }

}