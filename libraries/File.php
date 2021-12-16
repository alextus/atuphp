<?php

define('ATU_File_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . "File" . DIRECTORY_SEPARATOR);

require_once ATU_File_PATH . "Docx.php";

class ATU_File
{
    public $root_path;
    public $memory_limit = 0;

    public $filename;

    public $fp;
    public $info;

    public function __construct($ROOT_PATH = "../../Data/upload/")
    {
        $this->root_path = $ROOT_PATH;
        $this->domin = $ROOT_PATH == "../../Data/upload/" ? "http://res.alextu.cn/" : "upload/";
        $this->ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );

        $this->value = '';
        $this->mime_arr = array(
            '7z'  => 'application/x-7z-compressed',
            'ai'  => 'application/postscript',
            'amr'  => 'audio/amr',
            'avi'  => 'video/x-msvideo',
            'bin'    =>    'application/macbinary',
            'bmp'  => 'image/bmp',
            'bz'  => 'application/x-bzip2',
            'bz2'  => 'application/x-bzip2',
            'chm' => 'application/octet-stream',
            'class'    =>    'application/octet-stream',
            'css'  => 'text/css',
            'csv'    =>    'application/vnd.ms-excel',
            'doc' => 'application/vnd.ms-word',
            'docx'    =>    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dll'    =>    'application/octet-stream',
            'eps' => 'application/postscript',
            'exe' => 'application/octet-stream',
            'flv'  => 'video/x-flv',
            'gif'  => 'image/gif',
            'gtar'    =>    'application/x-gtar',
            'gz'  => 'application/x-gzip',
            'gzip'  => 'application/gzip',
            'html' => 'text/html',
            'htm'  => 'text/html',
            'java' => 'text/x-java-source',
            'js'    =>    'application/x-javascript',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe'    =>    'image/pjpeg',
            'json' => 'text/json',
            'log'    =>    'text/plain',
            'mp3'  => 'audio/mpeg',
            'mid'  => 'audio/midi',
            'midi'    =>    'audio/midi',
            'mkv'  => 'video/x-matroska',
            'mov'  => 'video/quicktime',
            'movie'    =>    'video/x-sgi-movie',
            'mp3'    =>    'audio/mpeg',
            'mp4'  => 'video/mp4',
            'mpg'  => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'ogg'  => 'audio/ogg',
            'png'  => 'image/png',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pdf'  => 'application/pdf',
            'php'  => 'text/x-php', //	'application/x-httpd-php',
            'phps'    =>    'application/x-httpd-php-source',
            'ps'    =>    'application/postscript',
            'psd'    =>    'application/octet-stream',
            'py'  => 'text/x-python',
            'qt'    =>    'video/quicktime',
            'rb'  => 'text/x-ruby',
            'rar'  => 'application/x-rar', //application/vnd.rar'
            'rtf'  => 'text/rtf',
            'rm'    => 'application/vnd.rn-realmedia',
            'smi'    =>    'application/smil',
            'smil'    =>    'application/smil',
            'sh'  => 'text/x-shellscript',
            'shtml'    =>    'text/html',
            'so'    =>    'application/octet-stream',
            'svg'    => 'image/svg+xml',
            'swf'  => 'application/x-shockwave-flash',
            'sql'  => 'text/x-sql',
            'tar'  => 'application/x-tar',
            'tga'  => 'image/x-targa',
            'tgz'  => 'application/x-compressed',
            'torrent'    =>    'application/x-bittorrent',
            'ts'    =>    'video/MP2T',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
            'txt'  => 'text/plain',
            'wav'  => 'audio/wav',
            'wma'  => 'audio/x-ms-wma',
            'xhtml'    =>    'application/xhtml+xml',
            'xht'    =>    'application/xhtml+xml',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx'    =>    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml'    =>    'text/xml',
            'xsl'    =>    'text/xml',
            'xz'  => 'application/x-xz',
            ''    =>    '',
            'zip'  => 'application/x-zip-compressed' //application/zip
        );
        $this->max_size = 30000000;
    }
    public function __call($function, $args)
    {
        echo $function . "<br/>";
        array_unshift($args, $this->value);
        $this->value = call_user_func_array($function, $args);
        return $this;
    }
    public function get_upload_max_size()
    {
        return min(intval(get_cfg_var('upload_max_filesize')), intval(get_cfg_var('post_max_size')), intval(get_cfg_var('memory_limit')));
    }
    public function getMime($type)
    {
    }







    public function uploadImg($file, $type)
    {
        return $this->upload($file, $type, array('jpg', 'jpeg', 'gif', 'png'));
    }
    public function resizeImg()
    {
    }
    public function check_file($FILES)
    {
        return  $error;
    }
    private function upload_path()
    {
        $upload_path = $this->root_path . $this->day(); //上传文件的存放路径
        $this->checkDir($upload_path);
        return $upload_path;
    }
    private function upload_data($oName, $realType, $upload_path, $fileName, $tFile, $url = "")
    {
        $d["result"] = 1;
        $d["message"] = "上传成功";
        $d["oName"] = $oName;
        $d["Name"] = $fileName;
        $d["Path"] = str_replace($this->root_path, "", $upload_path);
        $d["Type"] = $realType;
        $d["Mime"] = $this->mime_arr[$realType];
        $file = file_get_contents($tFile);
        $d["Md5"] = md5($file);
        $d["Size"] = strlen($file);
        $d["Url"] = $url;
        $d["file"] = $this->domin . $d["Path"] . "/" . $fileName;
        return $d;
    }
    private function _post($k, $v = "")
    {
        return isset($_POST[$k]) ? $_POST[$k] : $v;
    }
    private function _get($k, $v = "")
    {
        return isset($_GET[$k]) ? $_GET[$k] : $v;
    }
    private function _request($k, $v = "")
    {
        $pv = $this->_post($k, $v);
        return $pv ? $pv : $this->_get($k, $v);
    }
    private function _cookie($name, $value = null)
    {
        if ($value == null) {
            return isset($_COOKIE[$name]) ? $_COOKIE[$name] : "";
        } else {
            setcookie($name, $value, 0, '/', '', false);
        }
    }
    public function getPostFile()
    {
        $d = array();
        $d["type"]    = $this->_post("uploadType", 1); //上传类型 1普通file方式上传
        $d["fn"]      = $this->_post("fn", "file");
        $d["name"]    = $this->_post("name");
        $d["size"]    = intval($this->_post("size"));
        $d["Md5"]     = $this->_post("md5");
        $d["mime"]    = $this->_post("type");
        $d["url"]     = $this->_post("url");

        return $d;
    }
    /**
     * 传统方式上传文件
     */
    public function upload($fd, $type = "", $allow_type = array("jpg", "jpeg", "gif", "png", "txt", "sql", "pdf", "zip", "rar", "doc", "xls", "ppt", "docx", "xlsx", "pptx", "mp3", "mp4", "swf", "otf", "ttf", "woff", "woff2", "eot", "svg", "ts", "exe"))
    {
        $d = array("result" => 0, "message" => "上传失败", "file" => "");
        $file = $_FILES[$fd["fn"]];
        $oName = $file['name'];
        $_cookie_file = $this->_cookie(md5($file['name'] . $file['size']));

        if ($_cookie_file) {
            return json_decode($_cookie_file, true);
        }

        /**
         * $file name/type/tmp_name,error,size
         */

        if (!$type) {
            $type = strtolower(substr($oName, strrpos($oName, '.') + 1)); //得到文件类型，并且都转化成小写
        }
        $error = null;
        if (empty($file)) {
            $error = '上传图片不存在';
        }
        if ($file['error']) {
            switch ($file['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
        }
        if ($error) {
            $d["message"] = "上传失败,错误原因:$error" . $file['error'];
        } else {
            //判断是否是通过HTTP POST上传的
            if (is_uploaded_file($file['tmp_name'])) {
                $realType = $this->get_file_type($file['tmp_name'], $oName);
                $mime   = $this->mime_arr[$realType];

                if (!in_array($type, $allow_type) && !in_array($realType, $allow_type)) {
                    $d["message"] = $oName . ":" . $type . ',realType:' . $realType . ',文件格式不允许';
                } else {
                    $upload_path = $this->upload_path();
                    $fileName =  time() . rand(1000, 9999) . "." . $type;
                    $tFile = $upload_path . "/" . $fileName;
                    //开始移动文件到相应的文件夹
                    if (move_uploaded_file($file['tmp_name'], $tFile)) {
                        //2021-7-20  $realType  字体文件无法获取

                        //@chmod($fileName, 0644);
                        $d = $this->upload_data($oName, $realType ? $realType : $type, $upload_path, $fileName, $tFile);
                        $this->_cookie(md5($file['name'] . $file['size']), json_encode($d));
                        //	$new_file_path=$filePath . "thumb_".$new_file_name;
                        // $this->img2thumb($file_path,$new_file_path,240,135);
                    } else {
                        $d["message"] = "移动失败";
                    }
                }
            } else {
                $d["message"] = "上传失败,错误原因:" . $file['error'];
            }
        }

        return $d;
    }
    /*
    * 网址方式上传
     */
    public function uploadURL($url)
    {
        $d = array("result" => 0, "message" => "上传失败", "file" => "");

        $file = file_get_contents($url);

        $urlArr = explode("/", $url);
        $oName = $urlArr[sizeof($urlArr) - 1];


        $type = "";
        if (strpos($oName, ".") > 0) {
            $type = strtolower(substr($oName, strrpos($oName, '.') + 1));
        }
        if($type==""){
            $type=$this->getOnlineFileType($url);
        }
        $upload_path = $this->upload_path();
        $fileName = md5($url) . "." . $type;
        $tFile = $upload_path . "/" . $fileName;
        if (!file_exists($tFile)) {
            file_put_contents($tFile, $file);
        }

        $d = $this->upload_data($oName, $type, $upload_path, $fileName, $tFile, $url);


        return $d;
    }
    public function getUrlHeader($url){
        stream_context_set_default( [
            'ssl' => [
                'verify_host' => false,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $d=get_headers($url,1); 
        $arr=array();
       
       
        foreach($d as $k=>$v){
            if($k=="0"){
                $arr["http"]=$v;
               
                preg_match('/(200|401|402|403|404|500)/', $v, $d2);
                $arr["code"]=$d2[0];
            }else{
                $arr[strtolower($k)]=$v;
                
            }
           
        }
        return $arr;
    }
    public function getOnlineFileType($url){
        $header=$this->getUrlHeader($url);
       
        if(isset($header["content-type"])){

            return $this->get_mime_type($header["content-type"]);
        }else{
            return "";
        }
       
    }
    public function httpcode($url, $type=1)
    {
        $t1 = microtime(true);
        $d=array();
        $d["url"]=$url;
        if ($type==1) {
           
            $array =  $this->getUrlHeader($url);
            if (empty($d["code"])) {
               
                $d["code"]='unkown';
                $d["message"]="无效url资源！";
            } else {
                $d["code"]=$array["code"];
              
            }
        } else {
            $ch = curl_init();
            $timeout = 10;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSLVERSION, 2);
            //curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            
            $contents = curl_exec($ch);
            $d2=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            if (false == $contents) {
                $d["code"]='unkown';
                $d["message"]=curl_error($ch);
            } else {
                $d["code"]=$d2;
            }
        }
        $t2 = microtime(true);
        $d["usetime"]=($t2-$t1)/1000000000;
        return $d;
    }
    /*
    * 获取网址favicon.ico
    */
    public function getShortIocn($url,$contents=""){
        if(!$contents){
            $contents=file_get_contents($url);
        }
        $urlArr=parse_url($url);
        $mainUrl=$urlArr["scheme"]."://".$urlArr["host"];


        preg_match('/<link.*?rel=".*?icon".*?href="(.*?)".*?>/', $contents,$icon);
      
        if(!empty($icon)){
            $url=$icon[1];
            if(substr($url,0,4)!="http"){
                if(substr($url,0,2)=="//"){
                    $url=$urlArr["scheme"].":".$url;
                }else{
                    $url=$mainUrl.(substr($url,0,1)!="/"?"/":"").$url;
                }
                
            }
           
        }else{
            $url=$mainUrl."/favicon.ico";
        }
       // echo $url;
        $array = @get_headers($url,1); 
        if(preg_match('/200/',$array[0])){ 
            return $url;
        }
    
        return "";    
    }
    /*
    * base64方式上传
     */
    public function uploadDataURL($img_data)
    {
        $d = array("result" => 0, "message" => "上传失败", "file" => "");
        $type = preg_replace("/^data:image\/([^;]*);base64,.*/is", "\$1", $img_data);
        $pic_content = preg_replace("/data:image\/[^;]*;base64,/is", "", $img_data);
        $file = base64_decode($pic_content);
        $realType = $type == 'jpeg' ? 'jpg' : $type;

        $upload_path = $this->upload_path();
        $fileName = md5($file) . "." . $realType;
        $tFile = $upload_path . "/" . $fileName;
        if (!file_exists($tFile)) {
            file_put_contents($tFile, $file);
        }
        $d = $this->upload_data("", $realType, $upload_path, $fileName, $tFile);

        return $d;
    }
    public function uploadDataBinary($data)
    {

        /*
        if(empty($GLOBALS['HTTP_RAW_POST_DATA'])){
                $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        }else{
            $content = $GLOBALS['HTTP_RAW_POST_DATA'];  // 需要php.ini设置
        }
        */
        $file = $data[$data["fn"]];
        $type = strtolower(substr($data["name"], strrpos($data["name"], '.') + 1));
        $realType = $type == 'jpeg' ? 'jpg' : $type;

        $upload_path = $this->upload_path();
        $fileName = $this->time() . mt_rand(10000, 99999) . "." . $realType;
        $tFile = $upload_path . "/" . $fileName;

        file_put_contents($tFile, $file, true);

        $d = $this->upload_data("", $realType, $upload_path, $fileName, $tFile);

        return $d;
    }

    /**
     * 生成缩略图
    等比例缩放，最小尺寸$width $height
     */
    public function img2thumb($src_img, $dst_img, $width = 75, $height = '')
    {
        if (!is_file($src_img)) {
            return false;
        }

        $ot = $this->get_file_type($dst_img);

        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $x = $y = 0;
        if ($height == "") {
            $dst_w = $width;
            $dst_h = intval($src_h * $width / $src_w);
        } else {
            $dst_h = $height;
            $dst_w = $width;

            if ($src_w > $width &&  $src_h > $height) {
                $bl = $src_w / $src_h;
                if ($width / $height > $bl) {
                    $dst_h = intval($width / $bl);
                } else {
                    $dst_w = $bl * $height;
                }
            } else {
                $dst_w = $src_w;
                $dst_h = $src_h;
            }
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($dst_w, $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);


        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        } else {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        imagepng($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);


        return true;
    }

    public function checkDir($folder)
    {
        $reval = false;
        if (!file_exists($folder)) {
            @umask(0);                                         //如果目录不存在则尝试创建该目录
            preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);  //将目录路径拆分成数组
            $base = ($atmp[0][0] == '/') ? '/' : '';           //如果第一个字符为/则当作物理路径处理

            /* 遍历包含路径信息的数组 */
            foreach ($atmp[1] as $val) {
                if ('' != $val) {
                    $base .= $val;
                    if ('..' == $val || '.' == $val) {
                        $base .= '/';
                        continue;               //如果目录为.或者..则直接补/继续下一个循环
                    }
                } else {
                    continue;
                }
                $base .= '/';

                if (!file_exists($base)) {
                    if (@mkdir(rtrim($base, '/'), 0777)) {
                        @chmod($base, 0777);
                        $reval = true;   //尝试创建目录，如果创建失败则继续循环
                    }
                }
            }
        } else {
            $reval = is_dir($folder);   //路径已经存在。返回该路径是不是一个目录
        }
        clearstatcache();
        return $reval;
    }
    //删除一个目录，包括它的内容。 unlink($sFile);
    public function destroyDir($dir, $virtual = false)
    {
        $ds = DIRECTORY_SEPARATOR;
        $dir = $virtual ? realpath($dir) : $dir;
        $dir = substr($dir, -1) == $ds ? substr($dir, 0, -1) : $dir;
        if (is_dir($dir) && $handle = opendir($dir)) {
            while ($file = readdir($handle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                } elseif (is_dir($dir . $ds . $file)) {
                    destroyDir($dir . $ds . $file);
                } else {
                    unlink($dir . $ds . $file);
                }
            }
            closedir($handle);
            rmdir($dir);
            return true;
        } else {
            return false;
        }
    }


    ///创建文件
    public function make_file($filePath, $Content)
    {
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $Content);
        } else {
            file_put_contents($filePath, $Content, FILE_APPEND);
        }
    }
    //删除文件
    public function destroyFile($sFile)
    {
        if (file_exists($sFile)) {
            unlink($sFile);
        }
    }
    public function del($file)
    {
        if (is_dir($file)) {
            $this->destroyDir($file);
        } else {
            $this->destroyFile($file);
        }
    }
    /**
     * 列出当前目录下的文件列表
     * $level 0 简单列表(当前目录下的文件和目录列表) 1，详细列表
     */
    public function list_files($dir, $level = 0)
    {
        $list = array();
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != ".." && $file != "Thumbs.db") {
                        $type = is_dir($dir . $file) ? 0 : 1;
                        $child = $type == 0 ? $this->list_files($dir . $file) : "";

                        if ($level == 0) {
                            $list[] = $file;
                        } else {
                            $file = array("type" => $type, "path" => $dir, "name" => $file, "file" => $dir . $file);
                            $list[] = $type == 0 ? array_merge($file, array("child" => $child)) : $file;
                        }
                    }
                }
                closedir($handle);
            }
        }
        return $list;
    }

    /**
     * 文件或目录权限检查函数
     *
     * @access          public
     * @param           string  $file_path   文件路径
     * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
     *
     * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
     *                          返回值在二进制计数法中，四位由高到低分别代表
     *                          可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
     */
    public function file_mode_info($file_path)
    {
        /* 如果不存在，则不可读、不可写、不可改 */
        if (!file_exists($file_path)) {
            return false;
        }

        $mark = 0;

        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            /* 测试文件 */
            $test_file = $file_path . '/cf_test.txt';

            /* 如果是目录 */
            if (is_dir($file_path)) {
                /* 检查目录是否可读 */
                $dir = @opendir($file_path);
                if ($dir === false) {
                    return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
                }
                if (@readdir($dir) !== false) {
                    $mark ^= 1; //目录可读 001，目录不可读 000
                }
                @closedir($dir);

                /* 检查目录是否可写 */
                $fp = @fopen($test_file, 'wb');
                if ($fp === false) {
                    return $mark; //如果目录中的文件创建失败，返回不可写。
                }
                if (@fwrite($fp, 'directory access testing.') !== false) {
                    $mark ^= 2; //目录可写可读011，目录可写不可读 010
                }
                @fclose($fp);

                @unlink($test_file);

                /* 检查目录是否可修改 */
                $fp = @fopen($test_file, 'ab+');
                if ($fp === false) {
                    return $mark;
                }
                if (@fwrite($fp, "modify test.\r\n") !== false) {
                    $mark ^= 4;
                }
                @fclose($fp);

                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false) {
                    $mark ^= 8;
                }
                @unlink($test_file);
            }
            /* 如果是文件 */ elseif (is_file($file_path)) {
                /* 以读方式打开 */
                $fp = @fopen($file_path, 'rb');
                if ($fp) {
                    $mark ^= 1; //可读 001
                }
                @fclose($fp);

                /* 试着修改文件 */
                $fp = @fopen($file_path, 'ab+');
                if ($fp && @fwrite($fp, '') !== false) {
                    $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
                }
                @fclose($fp);

                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false) {
                    $mark ^= 8;
                }
            }
        } else {
            if (@is_readable($file_path)) {
                $mark ^= 1;
            }

            if (@is_writable($file_path)) {
                $mark ^= 14;
            }
        }

        return $mark;
    }
    public function day()
    {
        return date("Y", time()) . "/" . date("md", time());
    }

    public function time()
    {
        return  time() - strtotime("1982-12-19");
    }
    /**
     * 检查文件类型
     *
     * @access      public
     * @param       string      filename            文件名
     * @return      string
     */
    public function get_file_type($file, $filename = "")
    {
        $str = $format = '';
        $extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
        $file = @fopen($file, 'rb');
        if ($file) {
            $str = @fread($file, 0x400); // 读取前 1024 个字节
            @fclose($file);
            if(in_array($extname,array("exe"))){
                $format=$extname;
            }
        } else {

            if (!stristr("|jpg|jpeg|gif|png|doc|xls|txt|zip|rar|ppt|pdf|rm|mid|wav|bmp|swf|chm|sql|cert|otf|ttf|eot|woff|woff2|", "|$extname|") === false) {
                return $extname;
            } else {
                return '*';
            }
        }

        if ($format == '' && strlen($str) >= 2) {
            if (substr($str, 0, 4) == 'MThd' && $extname != 'txt') {
                $format = 'mid';
            } elseif (substr($str, 0, 4) == 'RIFF' && $extname == 'wav') {
                $format = 'wav';
            } elseif (substr($str, 0, 3) == "\xFF\xD8\xFF") {
                $format = 'jpg';
            } elseif (substr($str, 0, 4) == 'GIF8' && $extname != 'txt') {
                $format = 'gif';
            } elseif (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
                $format = 'png';
            } elseif (substr($str, 0, 2) == 'BM' && $extname != 'txt') {
                $format = 'bmp';
            } elseif ((substr($str, 0, 3) == 'CWS' || substr($str, 0, 3) == 'FWS') && $extname != 'txt') {
                $format = 'swf';
            } elseif (substr($str, 0, 4) == "\xD0\xCF\x11\xE0") {   // D0CF11E == DOCFILE == Microsoft Office Document
                if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" || $extname == 'doc') {
                    $format = 'doc';
                } elseif (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xls') {
                    $format = 'xls';
                } elseif (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" || $extname == 'ppt') {
                    $format = 'ppt';
                }
            } elseif (substr($str, 0, 4) == "PK\x03\x04") {
                $format = 'zip';
            } elseif (substr($str, 0, 4) == 'Rar!' && $extname != 'txt') {
                $format = 'rar';
            } elseif (substr($str, 0, 4) == "\x25PDF") {
                $format = 'pdf';
            } elseif (substr($str, 0, 3) == "\x30\x82\x0A") {
                $format = 'cert';
            } elseif (substr($str, 0, 4) == 'ITSF' && $extname != 'txt') {
                $format = 'chm';
            } elseif (substr($str, 0, 4) == "\x2ERMF") {
                $format = 'rm';
            } elseif (substr($str, 0, 5) == "<?php") {
                $format = 'php';
            } elseif ($extname == 'sql') {
                $format = 'sql';
            } elseif ($extname == 'txt') {
                $format = 'txt';
            } else {
                //$format = substr($str ,0, 4);  不知名文件格式
            }
        }
        return $format;
    }
    /**
     * 获取文件后缀名,并判断是否合法
     *
     * @param string $file_name
     * @param array $allow_type
     * @return blob
     */
    public function get_file_suffix($file_name, $allow_type = array())
    {
        $file_suffix = strtolower(array_pop(explode('.', $file_name)));
        if (empty($allow_type)) {
            return $file_suffix;
        } else {
            if (in_array($file_suffix, $allow_type)) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function get_mime_type($mime)
    {
        $type = "";
        foreach ($this->mime_arr as $key => $val) {
            if ($val == $mime) {
                $type = $key;
                break;
            }
        }
        return $type;
    }
   

    private function _getMimeDetect()
    {

        if (class_exists('finfo')) {
            return 'finfo';
        } else if (function_exists('mime_content_type')) {
            return 'mime_content_type';
        } else if (function_exists('exec')) {
            $result = exec('file -ib ' . escapeshellarg(FILE));
            if (0 === strpos($result, 'text/x-php') or 0 === strpos($result, 'text/x-c++')) {
                return 'linux';
            }
            $result = exec('file -Ib ' . escapeshellarg(FILE));
            if (0 === strpos($result, 'text/x-php') or 0 === strpos($result, 'text/x-c++')) {
                return 'bsd';
            }
        }
        return 'internal';
    }
    public function _getMimeType($path)
    {
        global $mime;
        $fmime = $this->_getMimeDetect();
        switch ($fmime) {
            case 'finfo':
                $finfo = finfo_open(FILEINFO_MIME);
                if ($finfo)
                    $type = @finfo_file($finfo, $path);
                break;
            case 'mime_content_type':
                $type = mime_content_type($path);
                break;
            case 'linux':
                $type = exec('file -ib ' . escapeshellarg($path));
                break;
            case 'bsd':
                $type = exec('file -Ib ' . escapeshellarg($path));
                break;
            default:
                $pinfo = pathinfo($path);
                $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
                $type = isset($mime[$ext]) ? $mime[$ext] : 'unkown';
                break;
        }
        $type = explode(';', $type);
        //需要加上这段，因为如果使用mime_content_type函数来获取一个不存在的$path时会返回'application/octet-stream'
        if ($fmime != 'internal' and $type[0] == 'application/octet-stream') {
            $pinfo = pathinfo($path);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            if (!empty($ext) and !empty($mime[$ext])) {
                $type[0] = $mime[$ext];
            }
        }
        return $type[0];
    }
    public function getFileType($filename)
    {
        if ((is_readable($filename) || file_exists($filename)) && is_file($filename)) {
            $this->fp = fopen($filename, 'rb');

            echo get_resource_type($this->fp) . "<br/>"; //file  stream
        } else {
            $errormessagelist = array();
            if (!is_readable($filename)) {
                $errormessagelist[] = '!is_readable';
            }
            if (!is_file($filename)) {
                $errormessagelist[] = '!is_file';
            }
            if (!file_exists($filename)) {
                $errormessagelist[] = '!file_exists';
            }
            if (empty($errormessagelist)) {
                $errormessagelist[] = 'fopen failed';
            }
            throw new getid3_exception('Could not open "' . $filename . '" (' . implode('; ', $errormessagelist) . ')');
        }
        $formattest = fread($this->fp, 32774);
        //echo  $formattest;
        $determined_format = $this->getFileFormat($formattest, $filename);
        return $determined_format;
    }
   
    public function getFileInfo($filename)
    {
    }
    public function getFileFormat(&$filedata, $filename = '')
    {
        foreach ($this->getFileFormatArray() as $format_name => $info) {
            if (!empty($info['pattern']) && preg_match('#' . $info['pattern'] . '#s', $filedata)) {
                return $info;
            }
        }
        if (preg_match('#\\.mp[123a]$#i', $filename)) {
            $getFileFormatArray = $this->getFileFormatArray();
            $info = $getFileFormatArray['mp3'];
            $info['include'] = 'module.' . $info['group'] . '.' . $info['module'] . '.php';
            return $info;
        } elseif (preg_match('#\\.cue$#i', $filename) && preg_match('#FILE "[^"]+" (BINARY|MOTOROLA|AIFF|WAVE|MP3)#', $filedata)) {
            $getFileFormatArray = $this->getFileFormatArray();
            $info = $getFileFormatArray['cue'];
            $info['include']   = 'module.' . $info['group'] . '.' . $info['module'] . '.php';
            return $info;
        }

        return false;
    }
    /**
     * Return array containing information about all supported formats.
     *
     * @return array
     */
    public function getFileFormatArray()
    {
        static $format_info = array();
        if (empty($format_info)) {
            $format_info = array(

                // Audio formats
                // AC-3   - audio      - Dolby AC-3 / Dolby Digital
                'ac3'  => array(
                    'pattern'   => '^\\x0B\\x77',
                    'group'     => 'audio',
                    'module'    => 'ac3',
                    'mime_type' => 'audio/ac3'
                ),
                // AAC  - audio       - Advanced Audio Coding (AAC) - ADIF format
                'adif' => array(
                    'pattern'   => '^ADIF',
                    'group'     => 'audio',
                    'module'    => 'aac',
                    'mime_type' => 'audio/aac',
                    'fail_ape'  => 'WARNING',
                ),

                // AA   - audio       - Audible Audiobook
                'aa'   => array(
                    'pattern'   => '^.{4}\\x57\\x90\\x75\\x36',
                    'group'     => 'audio',
                    'module'    => 'aa',
                    'mime_type' => 'audio/audible',
                ),

                // AAC  - audio       - Advanced Audio Coding (AAC) - ADTS format (very similar to MP3)
                'adts' => array(
                    'pattern'   => '^\\xFF[\\xF0-\\xF1\\xF8-\\xF9]',
                    'group'     => 'audio',
                    'module'    => 'aac',
                    'mime_type' => 'audio/aac',
                    'fail_ape'  => 'WARNING',
                ),

                // AU   - audio       - NeXT/Sun AUdio (AU)
                'au'   => array(
                    'pattern'   => '^\\.snd',
                    'group'     => 'audio',
                    'module'    => 'au',
                    'mime_type' => 'audio/basic',
                ),

                // AMR  - audio       - Adaptive Multi Rate
                'amr'  => array(
                    'pattern'   => '^\\x23\\x21AMR\\x0A', // #!AMR[0A]
                    'group'     => 'audio',
                    'module'    => 'amr',
                    'mime_type' => 'audio/amr',
                ),

                // AVR  - audio       - Audio Visual Research
                'avr'  => array(
                    'pattern'   => '^2BIT',
                    'group'     => 'audio',
                    'module'    => 'avr',
                    'mime_type' => 'application/octet-stream',
                ),

                // BONK - audio       - Bonk v0.9+
                'bonk' => array(
                    'pattern'   => '^\\x00(BONK|INFO|META| ID3)',
                    'group'     => 'audio',
                    'module'    => 'bonk',
                    'mime_type' => 'audio/xmms-bonk',
                ),

                // DSF  - audio       - Direct Stream Digital (DSD) Storage Facility files (DSF) - https://en.wikipedia.org/wiki/Direct_Stream_Digital
                'dsf'  => array(
                    'pattern'   => '^DSD ',  // including trailing space: 44 53 44 20
                    'group'     => 'audio',
                    'module'    => 'dsf',
                    'mime_type' => 'audio/dsd',
                ),

                // DSS  - audio       - Digital Speech Standard
                'dss'  => array(
                    'pattern'   => '^[\\x02-\\x08]ds[s2]',
                    'group'     => 'audio',
                    'module'    => 'dss',
                    'mime_type' => 'application/octet-stream',
                ),

                // DSDIFF - audio     - Direct Stream Digital Interchange File Format
                'dsdiff' => array(
                    'pattern'   => '^FRM8',
                    'group'     => 'audio',
                    'module'    => 'dsdiff',
                    'mime_type' => 'audio/dsd',
                ),

                // DTS  - audio       - Dolby Theatre System
                'dts'  => array(
                    'pattern'   => '^\\x7F\\xFE\\x80\\x01',
                    'group'     => 'audio',
                    'module'    => 'dts',
                    'mime_type' => 'audio/dts',
                ),

                // FLAC - audio       - Free Lossless Audio Codec
                'flac' => array(
                    'pattern'   => '^fLaC',
                    'group'     => 'audio',
                    'module'    => 'flac',
                    'mime_type' => 'audio/flac',
                ),

                // LA   - audio       - Lossless Audio (LA)
                'la'   => array(
                    'pattern'   => '^LA0[2-4]',
                    'group'     => 'audio',
                    'module'    => 'la',
                    'mime_type' => 'application/octet-stream',
                ),

                // LPAC - audio       - Lossless Predictive Audio Compression (LPAC)
                'lpac' => array(
                    'pattern'   => '^LPAC',
                    'group'     => 'audio',
                    'module'    => 'lpac',
                    'mime_type' => 'application/octet-stream',
                ),

                // MIDI - audio       - MIDI (Musical Instrument Digital Interface)
                'midi' => array(
                    'pattern'   => '^MThd',
                    'group'     => 'audio',
                    'module'    => 'midi',
                    'mime_type' => 'audio/midi',
                ),

                // MAC  - audio       - Monkey's Audio Compressor
                'mac'  => array(
                    'pattern'   => '^MAC ',
                    'group'     => 'audio',
                    'module'    => 'monkey',
                    'mime_type' => 'audio/x-monkeys-audio',
                ),


                // MOD  - audio       - MODule (Impulse Tracker)
                'it'   => array(
                    'pattern'   => '^IMPM',
                    'group'     => 'audio',
                    'module'    => 'mod',
                    //'option'    => 'it',
                    'mime_type' => 'audio/it',
                ),

                // MOD  - audio       - MODule (eXtended Module, various sub-formats)
                'xm'   => array(
                    'pattern'   => '^Extended Module',
                    'group'     => 'audio',
                    'module'    => 'mod',
                    //'option'    => 'xm',
                    'mime_type' => 'audio/xm',
                ),

                // MOD  - audio       - MODule (ScreamTracker)
                's3m'  => array(
                    'pattern'   => '^.{44}SCRM',
                    'group'     => 'audio',
                    'module'    => 'mod',
                    //'option'    => 's3m',
                    'mime_type' => 'audio/s3m',
                ),

                // MPC  - audio       - Musepack / MPEGplus
                'mpc'  => array(
                    'pattern'   => '^(MPCK|MP\\+|[\\x00\\x01\\x10\\x11\\x40\\x41\\x50\\x51\\x80\\x81\\x90\\x91\\xC0\\xC1\\xD0\\xD1][\\x20-\\x37][\\x00\\x20\\x40\\x60\\x80\\xA0\\xC0\\xE0])',
                    'group'     => 'audio',
                    'module'    => 'mpc',
                    'mime_type' => 'audio/x-musepack',
                ),

                // MP3  - audio       - MPEG-audio Layer 3 (very similar to AAC-ADTS)
                'mp3'  => array(
                    'pattern'   => '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]',
                    'group'     => 'audio',
                    'module'    => 'mp3',
                    'mime_type' => 'audio/mpeg',
                ),

                // OFR  - audio       - OptimFROG
                'ofr'  => array(
                    'pattern'   => '^(\\*RIFF|OFR)',
                    'group'     => 'audio',
                    'module'    => 'optimfrog',
                    'mime_type' => 'application/octet-stream',
                ),

                // RKAU - audio       - RKive AUdio compressor
                'rkau' => array(
                    'pattern'   => '^RKA',
                    'group'     => 'audio',
                    'module'    => 'rkau',
                    'mime_type' => 'application/octet-stream',
                ),

                // SHN  - audio       - Shorten
                'shn'  => array(
                    'pattern'   => '^ajkg',
                    'group'     => 'audio',
                    'module'    => 'shorten',
                    'mime_type' => 'audio/xmms-shn',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // TAK  - audio       - Tom's lossless Audio Kompressor
                'tak'  => array(
                    'pattern'   => '^tBaK',
                    'group'     => 'audio',
                    'module'    => 'tak',
                    'mime_type' => 'application/octet-stream',
                ),

                // TTA  - audio       - TTA Lossless Audio Compressor (http://tta.corecodec.org)
                'tta'  => array(
                    'pattern'   => '^TTA',  // could also be '^TTA(\\x01|\\x02|\\x03|2|1)'
                    'group'     => 'audio',
                    'module'    => 'tta',
                    'mime_type' => 'application/octet-stream',
                ),

                // VOC  - audio       - Creative Voice (VOC)
                'voc'  => array(
                    'pattern'   => '^Creative Voice File',
                    'group'     => 'audio',
                    'module'    => 'voc',
                    'mime_type' => 'audio/voc',
                ),

                // VQF  - audio       - transform-domain weighted interleave Vector Quantization Format (VQF)
                'vqf'  => array(
                    'pattern'   => '^TWIN',
                    'group'     => 'audio',
                    'module'    => 'vqf',
                    'mime_type' => 'application/octet-stream',
                ),

                // WV  - audio        - WavPack (v4.0+)
                'wv'   => array(
                    'pattern'   => '^wvpk',
                    'group'     => 'audio',
                    'module'    => 'wavpack',
                    'mime_type' => 'application/octet-stream',
                ),


                // Audio-Video formats

                // ASF  - audio/video - Advanced Streaming Format, Windows Media Video, Windows Media Audio
                'asf'  => array(
                    'pattern'   => '^\\x30\\x26\\xB2\\x75\\x8E\\x66\\xCF\\x11\\xA6\\xD9\\x00\\xAA\\x00\\x62\\xCE\\x6C',
                    'group'     => 'audio-video',
                    'module'    => 'asf',
                    'mime_type' => 'video/x-ms-asf',
                    'iconv_req' => false,
                ),

                // BINK - audio/video - Bink / Smacker
                'bink' => array(
                    'pattern'   => '^(BIK|SMK)',
                    'group'     => 'audio-video',
                    'module'    => 'bink',
                    'mime_type' => 'application/octet-stream',
                ),

                // FLV  - audio/video - FLash Video
                'flv' => array(
                    'pattern'   => '^FLV[\\x01]',
                    'group'     => 'audio-video',
                    'module'    => 'flv',
                    'mime_type' => 'video/x-flv',
                ),

                // IVF - audio/video - IVF
                'ivf' => array(
                    'pattern'   => '^DKIF',
                    'group'     => 'audio-video',
                    'module'    => 'ivf',
                    'mime_type' => 'video/x-ivf',
                ),

                // MKAV - audio/video - Mastroka
                'matroska' => array(
                    'pattern'   => '^\\x1A\\x45\\xDF\\xA3',
                    'group'     => 'audio-video',
                    'module'    => 'matroska',
                    'mime_type' => 'video/x-matroska', // may also be audio/x-matroska
                ),

                // MPEG - audio/video - MPEG (Moving Pictures Experts Group)
                'mpeg' => array(
                    'pattern'   => '^\\x00\\x00\\x01[\\xB3\\xBA]',
                    'group'     => 'audio-video',
                    'module'    => 'mpeg',
                    'mime_type' => 'video/mpeg',
                ),

                // NSV  - audio/video - Nullsoft Streaming Video (NSV)
                'nsv'  => array(
                    'pattern'   => '^NSV[sf]',
                    'group'     => 'audio-video',
                    'module'    => 'nsv',
                    'mime_type' => 'application/octet-stream',
                ),

                // Ogg  - audio/video - Ogg (Ogg-Vorbis, Ogg-FLAC, Speex, Ogg-Theora(*), Ogg-Tarkin(*))
                'ogg'  => array(
                    'pattern'   => '^OggS',
                    'group'     => 'audio',
                    'module'    => 'ogg',
                    'mime_type' => 'application/ogg',
                    'fail_id3'  => 'WARNING',
                    'fail_ape'  => 'WARNING',
                ),

                // QT   - audio/video - Quicktime
                'quicktime' => array(
                    'pattern'   => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)',
                    'group'     => 'audio-video',
                    'module'    => 'quicktime',
                    'mime_type' => 'video/quicktime',
                ),

                // RIFF - audio/video - Resource Interchange File Format (RIFF) / WAV / AVI / CD-audio / SDSS = renamed variant used by SmartSound QuickTracks (www.smartsound.com) / FORM = Audio Interchange File Format (AIFF)
                'riff' => array(
                    'pattern'   => '^(RIFF|SDSS|FORM)',
                    'group'     => 'audio-video',
                    'module'    => 'riff',
                    'mime_type' => 'audio/wav',
                    'fail_ape'  => 'WARNING',
                ),

                // Real - audio/video - RealAudio, RealVideo
                'real' => array(
                    'pattern'   => '^\\.(RMF|ra)',
                    'group'     => 'audio-video',
                    'module'    => 'real',
                    'mime_type' => 'audio/x-realaudio',
                ),

                // SWF - audio/video - ShockWave Flash
                'swf' => array(
                    'pattern'   => '^(F|C)WS',
                    'group'     => 'audio-video',
                    'module'    => 'swf',
                    'mime_type' => 'application/x-shockwave-flash',
                ),

                // TS - audio/video - MPEG-2 Transport Stream
                'ts' => array(
                    'pattern'   => '^(\\x47.{187}){10,}', // packets are 188 bytes long and start with 0x47 "G".  Check for at least 10 packets matching this pattern
                    'group'     => 'audio-video',
                    'module'    => 'ts',
                    'mime_type' => 'video/MP2T',
                ),

                // WTV - audio/video - Windows Recorded TV Show
                'wtv' => array(
                    'pattern'   => '^\\xB7\\xD8\\x00\\x20\\x37\\x49\\xDA\\x11\\xA6\\x4E\\x00\\x07\\xE9\\x5E\\xAD\\x8D',
                    'group'     => 'audio-video',
                    'module'    => 'wtv',
                    'mime_type' => 'video/x-ms-wtv',
                ),


                // Still-Image formats

                // BMP  - still image - Bitmap (Windows, OS/2; uncompressed, RLE8, RLE4)
                'bmp'  => array(
                    'pattern'   => '^BM',
                    'group'     => 'graphic',
                    'module'    => 'bmp',
                    'mime_type' => 'image/bmp',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // GIF  - still image - Graphics Interchange Format
                'gif'  => array(
                    'pattern'   => '^GIF',
                    'group'     => 'graphic',
                    'module'    => 'gif',
                    'mime_type' => 'image/gif',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // JPEG - still image - Joint Photographic Experts Group (JPEG)
                'jpg'  => array(
                    'pattern'   => '^\\xFF\\xD8\\xFF',
                    'group'     => 'graphic',
                    'module'    => 'jpg',
                    'mime_type' => 'image/jpeg',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // PCD  - still image - Kodak Photo CD
                'pcd'  => array(
                    'pattern'   => '^.{2048}PCD_IPI\\x00',
                    'group'     => 'graphic',
                    'module'    => 'pcd',
                    'mime_type' => 'image/x-photo-cd',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // PNG  - still image - Portable Network Graphics (PNG)
                'png'  => array(
                    'pattern'   => '^\\x89\\x50\\x4E\\x47\\x0D\\x0A\\x1A\\x0A',
                    'group'     => 'graphic',
                    'module'    => 'png',
                    'mime_type' => 'image/png',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // SVG  - still image - Scalable Vector Graphics (SVG)
                'svg'  => array(
                    'pattern'   => '(<!DOCTYPE svg PUBLIC |xmlns="http://www\\.w3\\.org/2000/svg")',
                    'group'     => 'graphic',
                    'module'    => 'svg',
                    'mime_type' => 'image/svg+xml',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // TIFF - still image - Tagged Information File Format (TIFF)
                'tiff' => array(
                    'pattern'   => '^(II\\x2A\\x00|MM\\x00\\x2A)',
                    'group'     => 'graphic',
                    'module'    => 'tiff',
                    'mime_type' => 'image/tiff',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // EFAX - still image - eFax (TIFF derivative)
                'efax'  => array(
                    'pattern'   => '^\\xDC\\xFE',
                    'group'     => 'graphic',
                    'module'    => 'efax',
                    'mime_type' => 'image/efax',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // Data formats

                // ISO  - data        - International Standards Organization (ISO) CD-ROM Image
                'iso'  => array(
                    'pattern'   => '^.{32769}CD001',
                    'group'     => 'misc',
                    'module'    => 'iso',
                    'mime_type' => 'application/octet-stream',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                    'iconv_req' => false,
                ),

                // HPK  - data        - HPK compressed data
                'hpk'  => array(
                    'pattern'   => '^BPUL',
                    'group'     => 'archive',
                    'module'    => 'hpk',
                    'mime_type' => 'application/octet-stream',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // RAR  - data        - RAR compressed data
                'rar'  => array(
                    'pattern'   => '^Rar\\!',
                    'group'     => 'archive',
                    'module'    => 'rar',
                    'mime_type' => 'application/vnd.rar',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // SZIP - audio/data  - SZIP compressed data
                'szip' => array(
                    'pattern'   => '^SZ\\x0A\\x04',
                    'group'     => 'archive',
                    'module'    => 'szip',
                    'mime_type' => 'application/octet-stream',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // TAR  - data        - TAR compressed data
                'tar'  => array(
                    'pattern'   => '^.{100}[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20\\x00]{12}[0-9\\x20\\x00]{12}',
                    'group'     => 'archive',
                    'module'    => 'tar',
                    'mime_type' => 'application/x-tar',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // GZIP  - data        - GZIP compressed data
                'gz'  => array(
                    'pattern'   => '^\\x1F\\x8B\\x08',
                    'group'     => 'archive',
                    'module'    => 'gzip',
                    'mime_type' => 'application/gzip',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // ZIP  - data         - ZIP compressed data
                'zip'  => array(
                    'pattern'   => '^PK\\x03\\x04',
                    'group'     => 'archive',
                    'module'    => 'zip',
                    'mime_type' => 'application/zip',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // XZ   - data         - XZ compressed data
                'xz'  => array(
                    'pattern'   => '^\\xFD7zXZ\\x00',
                    'group'     => 'archive',
                    'module'    => 'xz',
                    'mime_type' => 'application/x-xz',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),


                // Misc other formats

                // PAR2 - data        - Parity Volume Set Specification 2.0
                'par2' => array(
                    'pattern'   => '^PAR2\\x00PKT',
                    'group'     => 'misc',
                    'module'    => 'par2',
                    'mime_type' => 'application/octet-stream',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // PDF  - data        - Portable Document Format
                'pdf'  => array(
                    'pattern'   => '^\\x25PDF',
                    'group'     => 'misc',
                    'module'    => 'pdf',
                    'mime_type' => 'application/pdf',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // MSOFFICE  - data   - ZIP compressed data
                'msoffice' => array(
                    'pattern'   => '^\\xD0\\xCF\\x11\\xE0\\xA1\\xB1\\x1A\\xE1', // D0CF11E == DOCFILE == Microsoft Office Document
                    'group'     => 'misc',
                    'module'    => 'msoffice',
                    'mime_type' => 'application/octet-stream',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // TORRENT             - .torrent
                'torrent' => array(
                    'pattern'   => '^(d8\\:announce|d7\\:comment)',
                    'group'     => 'misc',
                    'module'    => 'torrent',
                    'mime_type' => 'application/x-bittorrent',
                    'fail_id3'  => 'ERROR',
                    'fail_ape'  => 'ERROR',
                ),

                // CUE  - data       - CUEsheet (index to single-file disc images)
                'cue' => array(
                    'pattern'   => '', // empty pattern means cannot be automatically detected, will fall through all other formats and match based on filename and very basic file contents
                    'group'     => 'misc',
                    'module'    => 'cue',
                    'mime_type' => 'application/octet-stream',
                ),

            );
        }

        return $format_info;
    }
    public function getFileLine($filePath, $split = "")
    {
        $lineArr = array();
        $fp = fopen($filePath, "r");
        if ($fp) {
            //stream_get_line 
            /**
             * stream_get_line()函数与fgets（）几乎相同，只是它允许除标准、标准和\r\n之外的行尾分隔符，并且不返回分隔符本身
             * 网络包，最长8192，8K
             */
            while (!feof($fp)) {
                $lineArr[] = $split ? stream_get_line($fp, 8192, $split) : fgets($fp, 8192);
            }
            fclose($fp);
        }
        return  $lineArr;
    }
    public function getDocxHtml($file)
    {
        $text = new ATU_Docx();
        $docx = $text->readDocument($file);
        return $docx;
    }
    public function getDocxText($file)
    {
        $text = new ATU_Docx();
        // 加载docx文件
        $text->setDocx($file);
        // 将内容存入$docx变量中
        $docx = $text->extract();
        return $docx;
        $content = "";
        $zip = new ZipArchive();
        if ($zip->open($file) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (pathinfo($entry, PATHINFO_BASENAME) == "document.xml") {
                    $zip->extractTo(pathinfo($file, PATHINFO_DIRNAME) . "/" . pathinfo($file, PATHINFO_FILENAME), array(
                        $entry
                    ));
                    $filepath = pathinfo($file, PATHINFO_DIRNAME) . "/" . pathinfo($file, PATHINFO_FILENAME) . "/" . $entry;
                    $content = file_get_contents($filepath);
                    break;
                }
            }

            $zip->close();
            return $content;
        } else {
            return '';
        }
    }
}
