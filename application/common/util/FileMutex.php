<?php

/**
 * Description of FileMutex
 *
 * @author ocean
 */

namespace app\common\util;

class FileMutex {
    
    /**
     * 文件锁路径
     * @var string
     */
    public $mutexPath = RUNTIME_PATH . 'Mutex';
    /**
     * 文件锁路径权限
     * @var integer
     */
    public $dirMode = 0775;
    
    /**
     * 为新创建的互斥体文件设置的权限。
     * 这个值将被PHP chmod（）函数使用。 不会应用umask。如：777
     * 如果未设置，则权限将由当前环境决定。
     * @var int 
     */
    public $fileMode;
    /**
     * 存储所有锁定文件。 键是锁名，值是文件。
     * @var string[] 
     */
    private $_files = [];
    /**
     * 当前PHP进程获取的锁的名称。
     * @var string[]
     */
    private $_locks = [];
    public function __construct($options = []) {
        if (!is_dir($this->mutexPath)) {
            self::createDirectory($this->mutexPath, $this->dirMode, true);
        }
    }
    
    /**
     * 锁定文件
     * @author Liuguiyang <lgy_js@163.com>
     * @version 创建时间：2017年5月5日 下午12:27:37 
     * @param unknown $name
     * @param number $timeout
     * @return boolean
     */
    public function acquire($name, $timeout = 0)
    {
        if ($this->acquireLock($name, $timeout)) {
            $this->_locks[] = $name;
    
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 解锁文件
     * @author Liuguiyang <lgy_js@163.com>
     * @version 创建时间：2017年5月5日 下午12:27:52 
     * @param unknown $name
     * @return boolean
     */
    public function release($name)
    {
        if ($this->releaseLock($name)) {
            $index = array_search($name, $this->_locks);
            if ($index !== false) {
                unset($this->_locks[$index]);
            }
    
            return true;
        } else {
            return false;
        }
    }
    
    protected function releaseLock($name)
    {
        if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
            return false;
        } else {
            fclose($this->_files[$name]);
            unlink($this->getLockFilePath($name));
            unset($this->_files[$name]);
    
            return true;
        }
    }
    
    /**
     * 创建锁定文件夹
     * @author Liuguiyang <lgy_js@163.com>
     * @version 创建时间：2017年5月5日 上午11:45:56 
     * @param unknown $path
     * @param number $mode
     * @param string $recursive
     * @throws E
     * @return boolean
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {
                throw E("文件夹创建失败 \"$path\": " . $e->getMessage(), $e->getCode());
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new E("更改文件夹权限失败 \"$path\": " . $e->getMessage(), $e->getCode());
        }
    }
    
    protected function acquireLock($name, $timeout = 0)
    {
        $file = fopen($this->getLockFilePath($name), 'w+');
        if ($file === false) {
            return false;
        }
        if ($this->fileMode !== null) {
            @chmod($this->getLockFilePath($name), $this->fileMode);
        }
        $waitTime = 0;
        while (!flock($file, LOCK_EX | LOCK_NB)) {
            $waitTime++;
            if ($waitTime > $timeout) {
                fclose($file);
    
                return false;
            }
            sleep(1);
        }
        $this->_files[$name] = $file;
    
        return true;
    }
    
    protected function getLockFilePath($name)
    {
        return $this->mutexPath . DIRECTORY_SEPARATOR . md5($name) . '.lock';
    }
}
