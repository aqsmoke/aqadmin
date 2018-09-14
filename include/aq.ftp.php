<?php

/**
 * IFeng kernel class - Ftp Class
 *
 * 在配置文件里加上初始化的数组
 * array(
 *    .....
 * ............
  'ftp' => array(
  'web' => array(
  'host' => '127.0.0.1',      //主机地址
  'post' => '',               //ftp端口,留空或者没有这项为21
  'user' => 'xiechengyu',     //ftp用户
  'password' => 'terry',      //ftp密码
  'timeout' => '',            //超时时间
  'pasv'   => true,           //是否以被动方式连接
  'type' => 'ftp' ,           // ftp  为普通ftp, ssl 为ftps
  )
  'image' => array(
  'host' => '192.168.1.199',      //主机地址
  'post' => '',               //ftp端口,留空或者没有这项为21
  'user' => 'xiechengyu',     //ftp用户
  'password' => 'terry',      //ftp密码
  'timeout' => '',            //超时时间
  'pasv'   => true,           //是否以被动方式连接
  'type' => 'ssl' ,           // ftp  为普通ftp, ssl 为ftps
  )
  )
 *   )
 *
 * @author xiechengyu
 * @package IFeng
 */
class Ftp {

    public $handle;  //ftp连接句柄
    private $config; //引入全局配置
    private $_instance;

    function __construct() {
        $this->config = array(
            'host' => '220.181.35.199',
            'post' => '',
            'user' => 'bbscms',
            'password' => 'gyg0sd',
            'timeout' => '',
            'pasv' => true,
            'type' => 'ftp'
        );
        if (!is_array($this->config)) {
            return $this->error('ftp 配置不存在');
        }

        empty($this->config['port']) && $this->config['port'] = 21;
        empty($this->config['pasv']) && $this->config['pasv'] = true;
        empty($this->config['timeout']) && $this->config['timeout'] = 90;
        empty($this->config['type']) && $this->config['type'] = 'ftp';

        $this->id = 'web';
    }

   

    /**
     * 获取类实例
     *
     * @param string $id容器标示符
     * @return object 返回对象
     */
    static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new Ftp();
        }
        return self::$_instance;
    }

    /**
     * 连接ftp
     *
     * @param string $id
     * @return resource
     */
    public function connect($id) {
        global $ifengInstances;
        if (!is_array($this->config)) {
            return $this->error('没有配置这个服务器 => ' . $id);
        }

        //$this->config['port'] =intval($ifengConfig['ftp'][$k]['port'])?intval($ifengConfig['ftp'][$k]['port']):21;

        if (isset($ifengInstances[__CLASS__ . 'connect' . $id])) {

            $this->handle = $ifengInstances[__CLASS__ . 'connect' . $id];
        } else {

            switch ($this->config['type']) {
                case 'ssl':
                    $this->handle = @ftp_ssl_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
                    break;
                default:
                    $this->handle = @ftp_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
                    break;
            }
        }

        if (!$this->handle) {
            return $this->error('ftp服务器 ' . $id . ' 连接失败');
        }

        if (!ftp_login($this->handle, $this->config['user'], $this->config['password'])) {
            return $this->error('ftp服务器 ' . $id . ' 用户验证失败');
        }

        if (!ftp_pasv($this->handle, (boolean) $this->config['pasv'])) {
            return $this->error('ftp pasv方式设置失败,请检查服务器设置');
        }
    }

    private function checkConnection() {
        global $ifengInstances;
        if (!is_resource($ifengInstances[__CLASS__ . $this->id])) {
            $this->connect($this->id);
        }
    }

    /**
     * 从ftp取得文件
     *
     * @param string $localFile 本地文件名或句柄
     * @param string $remotFile ftp文件名
     * @return boolean
     */
    public function get($localFile, $remotFile) {
        $this->checkConnection();
        if (is_resource($localFile)) {
            return ftp_fget($this->handle, $localFile, $remotFile, FTP_BINARY);
        } else {
            return ftp_get($this->handle, $localFile, $remotFile, FTP_BINARY);
        }
    }

    /**
     * 向ftp传送文件
     *
     * @param string $remotFile ftp文件名
     * @param string $localFile 本地文件名或句柄
     * @return boolean
     */
    public function put($remotFile, $localFile) {
        $this->checkConnection();
        $this->mkDir($remotFile, false);
        if (is_resource($localFile)) {
            return @ftp_fput($this->handle, $remotFile, $localFile, FTP_BINARY);
        } else {
            return @ftp_put($this->handle, $remotFile, $localFile, FTP_BINARY);
        }
    }

    /**
     * 取得ftp某个目录下所有文件/文件夹/链接的所有属性
     * 返回
     *     array(
     *               array('文件夹1'=>array('perms' =>'drwxrwxrwx',
     *                                     'permsn'=> '777',
     *                                     'number'=> '',
     *                                     'owner'=> 'xiecy',
     *                                     'group'=> 'root',
     *                                     'permsn'=> '',
     *                                                 ))
     *           )
     *
     *
     * @param string $dir ftp文件夹位置
     * @return array()
     */
    public function readDir($dir = '') {
        $this->checkConnection();
        $array = ftp_rawlist($this->handle, $dir);
        if (is_array($array)) {
            foreach ($array as $folder) {
                $struc = array();
                $current = preg_split("/[\s]+/", $folder, 9);

                $struc['perms'] = $current[0];
                $struc['permsn'] = $this->chmodNum($current[0]);
                $struc['number'] = $current[1];
                $struc['owner'] = $current[2];
                $struc['group'] = $current[3];
                $struc['size'] = $current[4];
                $struc['month'] = $current[5];
                $struc['day'] = $current[6];
                $struc['time'] = $current[7];
                $struc['name'] = str_replace('//', '', $current[8]);
                $struc['raw'] = $folder;

                if ($struc['name'] != '.' && $struc['name'] != '..' && $this->getType($struc['perms']) == "folder") {
                    $folders[] = $struc;
                } elseif ($struc['name'] != '.' && $struc['name'] != '..' && $this->getType($struc['perms']) == "link") {
                    $links[] = $struc;
                } elseif ($struc['name'] != '.' && $struc['name'] != '..') {
                    $files[] = $struc;
                }
            }
        }
        return array($folders, $links, $files);
    }

    private function chmodNum($mode) {
        $this->checkConnection();
        $realMode = "";
        $legal = array("", "w", "r", "x", "-");
        $attarray = preg_split("//", $mode);
        for ($i = 0; $i < count($attarray); $i++) {
            if ($key = array_search($attarray[$i], $legal)) {
                $realMode .= $legal[$key];
            }
        }
        $mode = str_pad($realMode, 9, '-');
        $trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
        $mode = strtr($mode, $trans);
        $newMode = '';
        $newMode .= $mode[0] + $mode[1] + $mode[2];
        $newMode .= $mode[3] + $mode[4] + $mode[5];
        $newMode .= $mode[6] + $mode[7] + $mode[8];
        return $newMode;
    }

    public function size($remoteFile) {
        $this->checkConnection();
        return ftp_size($this->handle, $remoteFile);
    }

    private function getType($perms) {
        if (substr($perms, 0, 1) == "d") {
            return 'folder';
        } elseif (substr($perms, 0, 1) == "l") {
            return 'link';
        } else {
            return 'file';
        }
    }

    public function mkDir($repath, $isdir = true) {
        $this->checkConnection();
        !$isdir && $path = dirname($repath);
        $dir = split("/", $path);
        $path = "";
        $res = true;
        for ($i = 1; $i < count($dir); $i++) {
            $path .= "/" . $dir[$i];
            if (!@ftp_chdir($this->handle, $path)) {
                @ftp_chdir($this->handle, "/");
                if (!@ftp_mkdir($this->handle, $path)) {
                    $res = false;
                    break;
                }
            }
        }
        return $res;
    }

    public function rmFile($fileName) {
        $this->checkConnection();
        return ftp_delete($this->handle, $fileName);
    }

    public function rmDir($dir) {
        $this->checkConnection();
        return ftp_rmdir($this->handle, $dir);
    }

    public function chmod($fileName) {
        $this->checkConnection();
        return ftp_chmod($this->handle, $mod, $fileName);
    }

    //在win下有问题.待解决
    public function stat($fileName) {
        $this->checkConnection();
        echo $fileName;
        $array = ftp_raw($this->handle, "stat " . $fileName . "\n");
        $struc = array();
        $current = preg_split("/[\s]+/", $array[2], 9);
        $struc['perms'] = $current[0];
        $struc['permsn'] = $this->chmodNum($current[0]);
        $struc['number'] = $current[1];
        $struc['owner'] = $current[2];
        $struc['group'] = $current[3];
        $struc['size'] = $current[4];
        $struc['month'] = $current[5];
        $struc['day'] = $current[6];
        $struc['time'] = $current[7];
        $struc['name'] = str_replace('//', '', $current[8]);
        $struc['raw'] = $folder;

        if ($struc['name'] != '.' && $struc['name'] != '..' && $this->getType($struc['perms']) == "folder") {
            $struc['type'] = 'folder';
        } elseif ($struc['name'] != '.' && $struc['name'] != '..' && $this->getType($struc['perms']) == "link") {
            $struc['type'] = 'link';
        } elseif ($struc['name'] != '.' && $struc['name'] != '..') {
            $struc['type'] = 'file';
        }
        return $struc;
    }

    function move() {
        
    }

    function rename() {
        return $this->move();
    }

    function cp() {
        
    }

    function close() {
        return ftp_close($this->handle);
    }

    function error($msg) {
        echo "<hr><p>$msg</p><hr>";
        die();
    }

}
