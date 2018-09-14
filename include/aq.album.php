<?php

class Album {

    private static $instance; //静态实例
    private static $upload; //上传
    private static $ftp; //ftp
    private static $mem; //memcache
    private static $image; //图片处理
    private $config; //配置

    /**
     * 初始化实例对象
     * @param $id 配置文件标签
     * 
     */

    public function __construct($id) {
        global $ifengConfig;
        $this->config = $ifengConfig['albums'][$id];

        if (!self::$upload) {
            self::$upload = IFengSystem::getInstance('IFengUpload', 'album');
        }
        if (!self::$ftp) {
            self::$ftp = IFengSystem::getInstance('IFengFtp', 'web');
        }
        if (!self::$image) {
            self::$image = IFengSystem::getInstance('IFengImage', 'album');
        }
        if (!self::$mem) {
            self::$mem = IFengSystem::getInstance('IFengMem', 'album');
        }
        if (!class_exists('ThreadsAndPosts')) {
            require DISCUZ_ROOT . './bbsDAO/ThreadsAndPosts.php';
        }
    }

    /**
     * 生成实例
     * @param $id 配置文件标签
     *
     * @return 实例化对象
     */
    public static function getInstance($id) {
        if (!self::$instance) {
            self::$instance = new Album($id);
        }
        return self::$instance;
    }

    /**
     * 对图片上传，缩放裁切，ftp到远程并入库操作
     *
     * @param Array $srcFile 图片上传信息数组
     * @param  String $httpPath php执行上传到web的路径
     * @param String $ftpPath ftp到远程的路径
     * @return 1 成功 -1图片不能上传 -2 上传图片太大 -3 上传图片格式不对 -4 生成缩图失败
     * 		-5 ftp到远程失败 -8 对原图缩放错误 
     */
    public function uploadPhoto($srcFile, $httpPath, $ftpPath) {
        global $db, $tablepre, $timestamp, $pid, $tid, $discuz_uid;
        $dest = array();
        $sql = $timePath = $destPath = $destFile = $ext = $thumbFile = $ftpName = $ftpThumbName = $width = $height = $type = $attr = '';
        //检测是否可上传
        if (!disuploadedfile($srcFile['tmp_name']) || !($srcFile['tmp_name'] != 'none' && $srcFile['tmp_name'] && $srcFile['name'])) {
            return -1;
        }
        $ext = strtolower(fileext($srcFile['name']));
        //http上传
        $timepath = date('Y-m', $timestamp);
        $hash = IFengSystem::random(8, true);
        $destFile = $hash . '_' . md5($srcFile['name']) . '.' . $ext;
        $thumbFile = $hash . '_' . md5($srcFile['name']) . 's.' . $ext;
        $destPath = $httpPath . '/' . $timepath;
        @mkdir(str_replace('//', '/', $destPath), 0755, true);
        $dest = self::$upload->upload($srcFile, $destPath . '/' . $destFile);

        if ($dest == -1) {
            return -2;
        }
        if (!$dest['isImage']) {
            return -3;
        }
        //生成小缩图
        $dest['thumbname'] = str_replace($destFile, $thumbFile, $dest['path']);
        if (!self::$image->thumbnailFile($dest['path'], $dest['thumbname'], $this->config['thumbWidth'], $this->config['thumbHeight'], $this->config['thumbType'])) {
            return -4;
        }

        //对原图超过指定大小缩到指定大小
        list($width, $height, $type, $attr) = getimagesize($dest['path']);
        if ($width > $this->config['width'] || $height > $this->config['height']) {
            if (!self::$image->thumbnailFile($dest['path'], $dest['path'], $this->config['width'], $this->config['height'], $this->config['type'])) {
                return -8;
            }
        }

        //ftp上传到cdn
        $ftpName = $ftpPath . '/' . $timepath . '/' . $destFile;
        $ftpThumbName = $ftpPath . '/' . $timepath . '/' . $thumbFile;
        if (!self::$ftp->put($ftpName, $dest['path']) || !self::$ftp->put($ftpThumbName, $dest['thumbname'])) {
            return -5;
        }
        @unlink($dest['path']);
        @unlink($dest['thumbname']);
        $photourl = addslashes(FILE_SITE_URL . '/bbsfile/cmsupload/albums/' . $timepath . '/' . $destFile);
        $thumburl = addslashes(FILE_SITE_URL . '/bbsfile/cmsupload/albums/' . $timepath . '/' . $thumbFile);

        if (!$db->numRows($db->query("SELECT * FROM {$tablepre}albums WHERE pid = '$pid'"))) {
            $sql = "REPLACE INTO {$tablepre}albums SET pid = '$pid', authorid='$discuz_uid', tid='$tid', num=num+1, cover='$thumburl', ctime='$timestamp'";
        } else {
            $sql = "UPDATE {$tablepre}albums SET num=num+1, utime='$timestamp' WHERE pid = '$pid'";
        }
        if (!$db->query($sql) || !$db->query("UPDATE {$tablepre}threads SET supe_pushstatus = 2 WHERE tid='$tid'")) {
            return -6;
        }
        $sql = "INSERT INTO {$tablepre}photos SET pid = '$pid', tid='$tid', uid='$discuz_uid', photourl='$photourl',
			thumburl = '$thumburl', ctime='$timestamp'";
        if (!$db->query($sql)) {
            return -7;
        }
        //更新缓存
        self::$mem->delete('album_' . $tid);
        ThreadsAndPosts::editPost($tid);
        return 1;
    }

    /**
     * 删除相册中单张图片
     *
     * @param Int $photoId 图片ID
     * @param Int $tid 主题ID
     * @return 1 成功  -1 删除图片表不成功
     */
    public function deletePhoto($photoId, $tid) {
        global $db, $tablepre, $timestamp;
        $sql = "UPDATE {$tablepre}photos SET isdelete = 1 WHERE photoid = " . $photoId;
        if (!$db->query($sql)) {
            return -1;
        }
        $Tsql = $db->fetchFirst("SELECT tid FROM {$tablepre}photos WHERE photoid = " . $photoId);
        $tid = $Tsql['tid'];
        //更新缓存
        self::$mem->delete('album_' . $tid);
        ThreadsAndPosts::editPost($tid);
        return 1;
    }

    /**
     * 保存图片说明信息
     * @param Int $photoId 图片ID
     * @param Int $tid
     * @param String $intro
     * @return 1 成功 -1 保存图片说明不成功
     */
    public function saveIntro($photoId, $tid, $intro) {
        global $db, $tablepre;
        $sql = "UPDATE {$tablepre}photos SET intro = '$intro' WHERE photoid = '$photoId'";
        if (!$db->query($sql)) {
            return -1;
        }
        //更新缓存
        self::$mem->delete('album_' . $tid);
        ThreadsAndPosts::editPost($tid);

        return 1;
    }

    /**
     * 设置封面
     *
     * @param Int $photoId 图片ID
     * @return 1 成功 -1不成功
     */
    public function setCover($photoId, $pid) {
        global $db, $tablepre, $timestamp;
        $thumburl = '';
        $thumburl = daddslashes($db->result($db->query("SELECT thumburl FROM {$tablepre}photos WHERE photoid = '$photoId'"), 0), true);
        $sql = "UPDATE {$tablepre}albums SET cover='$thumburl', coverid='$photoId' WHERE $pid = '$pid'";
        if (!$db->query($sql)) {
            return -1;
        }
        return 1;
    }

    /**
     * 获得相册列表
     *
     * @param Int $pid 帖子ID
     * @return 相册数组
     */
    public function getAlbumList($tid) {
        global $db, $tablepre;
        $key = 'album_' . $tid;
        $reponse = '';
        $res = array();
        $reponse = self::$mem->get($key);

        if (!$reponse) {
            //echo "no cache".'<hr />';
            $sql = "SELECT * FROM {$tablepre}photos WHERE tid = '$tid' AND isdelete = '0'";
            $query = $db->query($sql);
            while ($row = $db->fetchArray($query)) {
                $res['show'][$row['pid']][] = $row; //显示列表
                $res['page'][] = $row; //计算翻页
            }
            self::$mem->set($key, serialize($res));
        } else {
            //echo "cache".'<hr />';
            $res = unserialize($reponse);
        }
        return $res;
    }

    /**
     * 后台相册管理列表分页
     *
     * @param Int $tid
     * @param Int $p
     * @param Int $pagesize
     * @return Array
     */
    public function getAlbumAdmin($tid, $page=1, $pagesize = 20) {
        global $db, $tablepre;

        $res = array();

        $res['count'] = $db->result($db->query("SELECT count(*) FROM {$tablepre}photos WHERE tid = '$tid' AND isdelete = '0'"));
        $offset = ($page - 1) * $pagesize;

        $res['list'] = $db->fetchAll("SELECT * FROM {$tablepre}photos WHERE tid = '$tid' AND isdelete = '0' LIMIT $offset, $pagesize");

        return $res;
    }

    public function getNextPicNo($tid, $photoId) {
        $picArr = $this->getAlbumList($tid);
        $nextId = 0;
        if ($picArr['page']) {
            foreach ($picArr['page'] as $key => $row) {
                if ($key == $photoId) {
                    $nextId = $photoId + 1;
                    break;
                }
            }
        }
        $nextId >= count($picArr['page']) && $nextId = count($picArr['page']) - 1;
        return $nextId;
    }

    public function getPrevPicNo($tid, $photoId) {
        $picArr = $this->getAlbumList($tid);
        $prevId = 0;
        if ($picArr['page']) {
            foreach ($picArr['page'] as $key => $row) {
                if ($key == $photoId) {
                    $prevId = $photoId - 1;
                    break;
                }
            }
        }
        $prevId < 0 && $prevId = count($picArr['page']);
        return $prevId;
    }

    /**
     * 获得用户相册列表
     *
     * @param Int $uid
     * @return Array
     */
    public function getAuthorList($uid) {
        global $db, $tablepre;
        $res = array();
        $key = 'album_author' . $uid;
        $reponse = '';
        $reponse = self::$mem->get($key);
        if (!$reponse) {
            //echo "no cache".'<hr />';
            $sql = "SELECT pid, tid, cover FROM {$tablepre}albums WHERE authorid = '$uid'";
            $query = $db->query($sql);
            while ($row = $db->fetchArray($query)) {
                $res[] = $row;
            }
            self::$mem->set($key, serialize($res));
        } else {
            //echo "cache".'<hr />';
            $res = unserialize($reponse);
        }
        return $res;
    }

    /**
     * 获得后台CMS推荐幻灯
     * @param $blockid 模板ID
     * @return Array
     */
    public function getRecommedAlbum($blockid, $topnum=10) {
        global $db, $tablepre;
        $res = array();
        $key = 'album_recommend_' . $blockid;
        $reponse = '';
        $reponse = self::$mem->get($key);
        if (!$reponse) {
            //echo "no cache".'<hr />';
            $sql = "SELECT tid, title, photourl FROM {$tablepre}item WHERE blockid='$blockid' AND ispublish = 1 ORDER BY weight DESC, tid DESC LIMIT 0, $topnum";
            //echo $sql;exit;
            $query = $db->query($sql);
            while ($row = $db->fetchArray($query)) {
                $res[] = $row;
            }
            self::$mem->set($key, serialize($res));
        } else {
            //echo "cache".'<hr />';
            $res = unserialize($reponse);
        }
        return $res;
    }

    public function updateRecommedAlbumCache($blockid) {
        self::$mem->delete('album_recommend_' . $blockid);
    }

    /**
     * 获得相册信息
     *
     * @param Int $pid
     * @return Array
     */
    public function getAlbum($pid) {
        global $db, $tablepre;
        $res = array();

        $sql = "SELECT * FROM {$tablepre}albums WHERE pid = '$pid'";
        return $db->fetchFirst($sql);
    }

}

?>