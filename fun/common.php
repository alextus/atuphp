<?php
/**
 * 基础函数库,Core/Controller.php中引用
 *
 */

define('MAGIC_QUOTES_GPC', ini_set("magic_quotes_runtime", 0)?true:false);

function _get($str, $xss = true)
{
    return isset($_GET[$str])?_svar($_GET[$str], $xss):"";
}

function _post($str, $xss = true)
{
    return isset($_POST[$str])?_svar($_POST[$str], $xss):"";
}

function _cookie($str, $xss = false)
{
    return isset($_COOKIE[$str])?_svar($_COOKIE[$str], $xss):"";
}
function S_get($txt= "", $xss = true)
{
    $d = array();
    if (is_array($txt)) {
        foreach ($txt as $key) {
            $d[$key] = _get($key, $xss);
        }
    } else {
        if ($txt == "") {
            foreach ($_GET as $key) {
                $d[$key] = _get($key, $xss);
            }
        } else {
            $d = _get($txt, $xss);
        }
    }
    return $d;
}
function S_post($txt= "", $xss = true)
{
    $d = array();
    if (is_array($txt)) {
        foreach ($txt as $key) {
            $d[$key] = _post($key, $xss);
        }
    } else {
        if ($txt == "") {
            foreach ($_POST as $key => $val) {
                $d[$key] =  _post($key, $xss);
            }
        } else {
            $d =_post($txt, $xss);
        }
    }
    return $d;
}
function S_cookie($txt, $xss = false)
{
    $d = array();
    if (is_array($txt)) {
        foreach ($txt as $key) {
            $d[$key] = _cookie($key, $xss);
        }
    } else {
        if ($txt == "") {
            foreach ($_GET as $key) {
                $d[$key] = _cookie($key, $xss);
            }
        } else {
            $d = _cookie($txt, $xss);
        }
    }
    return $d;
}
function request($txt= "", $xss = true){
    return isset($_GET[$txt])?S_get($txt,$xss):S_post($txt,$xss);
}


function _svar($txt, $xss = false)
{
    return $xss?SQLFilter(trim($txt)):trim($txt);
}
function SQLFilter($txt)
{
    if (is_numeric($txt)) {
        return $txt;
    }
    if (is_null($txt)) {
        return "";
    }
    if (!MAGIC_QUOTES_GPC) {
        //$txt = addslashes($txt);
    }
    

   // $txt = str_replace("script", "&#115;cript", $txt);
    $txt = str_replace("SCRIPT", "&#083;CRIPT", $txt);
    $txt = str_replace("Script", "&#083;cript", $txt);
   // $txt = str_replace("script", "&#083;cript", $txt);
    $txt = str_replace("object", "&#111;bject", $txt);
    $txt = str_replace("OBJECT", "&#079;BJECT", $txt);
    $txt = str_replace("Object", "&#079;bject", $txt);
    $txt = str_replace("object", "&#079;bject", $txt);
    $txt = str_replace("applet", "&#097;pplet", $txt);
    $txt = str_replace("APPLET", "&#065;PPLET", $txt);
    $txt = str_replace("Applet", "&#065;pplet", $txt);
    $txt = str_replace("applet", "&#065;pplet", $txt);
    $txt = str_replace("select ", "sel&#101;ct ", $txt);
    $txt = str_replace("execute ", "&#101xecute ", $txt);
    $txt = str_replace("exec", "&#101xec", $txt);
    $txt = str_replace("join ", "jo&#105;n ", $txt);
    $txt = str_replace("union ", "un&#105;on ", $txt);
    $txt = str_replace("where ", "wh&#101;re ", $txt);
    $txt = str_replace("insert ", "ins&#101;rt ", $txt);
    $txt = str_replace("delete ", "del&#101;te ", $txt);
    $txt = str_replace("update ", "up&#100;ate ", $txt);
    $txt = str_replace("like ", "lik&#101; ", $txt);
    $txt = str_replace("drop ", "dro&#112; ", $txt);
    $txt = str_replace("create ", "cr&#101;ate ", $txt);
    $txt = str_replace("rename ", "ren&#097;me ", $txt);
    $txt = str_replace("exists ", "e&#120;ists ", $txt);
    $txt = str_replace("'", "&apos;", $txt);
    $txt = str_replace("`", "&apos;", $txt);

    return $txt;
}


function str_len($str)
{
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
    if ($length) {
        return strlen($str) - $length + intval($length / 3) * 2;
    } else {
        return strlen($str);
    }
}
function Str_left($String, $Length, $Append = false)
{
    //替换图片
    //$String = preg_replace("/(<img.*>)/",'', $String);
    //$String = preg_replace("/(<IMG.*>)/",'', $String);

    if ($Length == 0 || strlen($String) <= $Length) {
        return $String;
    } else {
        if (function_exists('mb_substr')) { 
            $newstr = mb_substr($String, 0, $Length, "utf-8");  //>4.0.6,
        } elseif (function_exists('iconv_substr')) {  //>5
            $newstr = iconv_substr($String, 0, $Length, "utf-8");
        } else {
            $newstr = substr($String, 0, $Length);
        }
        if ($Append) {
            $newstr .= "...";
        }
        return $newstr;
    }
}

function str2htm($txt)
{
    if (is_null($txt)) {
        $txt="";
    }

    $txt = str_replace("&", "&amp;", $txt);
    $txt = str_replace("<", "&lt;", $txt);
    $txt = str_replace(">", "&gt;", $txt);
    $txt = str_replace("'", "‘", $txt);
    
    
    $txt = str_replace(" ", "&nbsp;", $txt);
    $txt = str_replace("", "&nbsp;&nbsp;", $txt);
    $txt = str_replace("/n", "<br/>", $txt);
    $txt = str_replace(chr(13), "<br/>", $txt);
    $txt = str_replace(chr(10), "", $txt);
    $txt = str_replace(chr(0), "&nbsp;", $txt);
    $txt = str_replace(chr(7), "&nbsp;", $txt);
    $txt = str_replace(chr(9), "&nbsp;", $txt);
    $txt = str_replace(chr(11), "&nbsp;", $txt);
    $txt = str_replace(chr(12), "&nbsp;", $txt);
    $txt = str_replace(chr(255), "&nbsp;", $txt);
    
    return $txt;
}

function htm2str($txt)
{
    $txt = str_replace("&nbsp;", chr(0), $txt);
    $txt = str_replace("", chr(10), $txt);
    $txt = str_replace("<br/>", "/n", $txt);
    $txt = str_replace("<br/>", chr(13), $txt);
    $txt = str_replace("&nbsp;&nbsp;", "", $txt);
    $txt = str_replace("&nbsp;", " ", $txt);
    $txt = str_replace("‘", "'", $txt);
    $txt = str_replace("&gt;", ">", $txt);
    $txt = str_replace("&lt;", "<", $txt);
    $txt = str_replace("&amp;", "&", $txt);
    
    return $txt;
}

function strToArray($strs)
{
    $result = array();
    $array = array();
    $strs = str_replace('，', ',', $strs);
    $strs = str_replace(' ', ',', $strs);
    $array = explode(',', $strs);
    foreach ($array as $key => $value) {
        if ('' != ($value = trim($value))) {
            $result[] = $value;
        }
    }
    return $result;
}








function md5_16($str)
{
    return substr(md5($str), 8, 16);
}

function random($length)
{
    $hash = '';
    $chars='123456789alextuiyn';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

//---------------------------------------------------
//////////常用变量类型判断
function isNum($str)
{
    if (is_numeric($str)) {
        return true;
    } else {
        return false;
    }
}

/*** 验证输入的邮件地址是否合法*/
function isEmail($str)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($str, '@') !== false && strpos($str, '.') !== false) {
        if (preg_match($chars, $str)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
/*** 验证输入的手机是否合法*/
function isMobile($str)
{
    $chars = "/^0{0,1}(13[0-9]|14(0|1|5|6|7|9)|15([0-3]|[5-9])|(17([0-3]|[5-9]))|(18[0-9]))+\d{8}$/i";
 
    if (preg_match($chars, $str)) {
        return true;
    } else {
        return false;
    }
}

function url()
{
    return 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
}
/*** 检查是否为一个合法的时间格式*/
function isTime($str)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
    return preg_match($pattern, $str);
}

//---------------------------------------------------------------------------

function ua(){
	
	$ua=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
    $path=defined("path")?path:"";
	$ua_file= $path."data/ua/".md5($ua).".txt";
	if(!file_exists($ua_file)){
		file_put_contents($ua_file,$ua);
	}
	return $ua;
}
function platform(){
	$ua=ua();
	if(strpos($ua, 'MicroMessenger') !== false){
		$platform="weixin";
	}elseif(strpos($ua, 'Weibo')!== false){
		$platform="weibo";
	}elseif(strpos($ua, 'Eleme')!== false){
		$platform="eleme";
	}else{
		$platform="other";
	}
	return $platform;
}

function AlexCode(){
	$time=time()%100000000;
	$c=md5(AlexKey.$time);
	$c=substr($c,0,strlen($c)-8);
	return $c.$time;
}

function isAlexCode($v){
	$len=strlen($v);
	$time=substr($v,$len-8,$len);
	$md=substr($v,0,$len-8);
	$c=md5(AlexKey.$time);
	if($md==substr($c,0,strlen($c)-8)){
		return true;	
	}else{
		return false;
	}
}
//16位
function AlexUid(){
	$r=rand(100,999);
	$time=md5(time().$r);
	$time=substr($time,0,8);
	
	$c=md5(AlexKey.$time);
	$c=substr($c,0,4).$time;
	return $c;
}
function isAlexUid($v){
	$len=strlen($v);
	$time=substr($v,$len-8,$len);
	$md=substr($v,0,4);
	$c=md5(AlexKey.$time);
	if($md==substr($c,0,4)){
		return true;	
	}else{
		return false;
	}
	
}
///IP相关
/**** 获得用户的真实IP地址*/
function ip()
{
    static $realip = null;
    if ($realip !== null) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}
/*** 获取服务器的ip**/
function server_ip()
{
    static $serverip = null;
    if ($serverip !== null) {
        return $serverip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverip = $_SERVER['SERVER_ADDR'];
        } else {
            $serverip = '0.0.0.0';
        }
    } else {
        $serverip = getenv('SERVER_ADDR');
    }
    return $serverip;
}

function referer(){

   if(isset($_SERVER["HTTP_REFERER"])){
      return parse_url($_SERVER["HTTP_REFERER"])["host"];
   }else{
       return '';
   }
}

function getMacAddr(){
	$os_type=strtolower(PHP_OS) ;

	$d="";
	if($os_type=="linux"){
		@exec("ifconfig -a", $mac);
	}
	if($os_type=="winnt"){
		@exec("ipconfig /all", $mac);
		if ( !$mac ){
			$ipconfig = $_SERVER["WINDIR"]."system32ipconfig.exe";
			if ( is_file($ipconfig) ){
				@exec($ipconfig." /all", $mac);
			}else{
				@exec($_SERVER["WINDIR"]."systemipconfig.exe /all", $mac);
			}
		}
	}
	$temp_array = array();
	foreach ( $mac as $value )
	{
		if ( preg_match( "/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i", $value, $temp_array ) )
		{
			$mac_addr = $temp_array[0];
			break;
		}
	}
	unset($temp_array);
	return $mac_addr;
}

//-------------------------------------------------------------------------------------
////时间 相关

function Alexdate()
{
    return date("Ymd", time());
}
function AlexDay()
{
    return Alexdate();
}
function AlexYm()
{
    return date("Ym", time());
}
function Alextime()
{
    return  time()-strtotime("1982-12-19");
}



function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1)+floatval($t2))*1000);
}

function FormatNum($num, $weishu)
{
    $FormatNum=$num;
    
    for ($i=strlen($num);$i<$weishu;$i++) {
        $FormatNum="0".$FormatNum;
    }
    return $FormatNum;
}

function year()
{
    return date("Y", time());
}
function month()
{
    return date("m", time());
}
function day()
{
    return date("d", time());
}
function hour()
{
    return date("H", time());
}
function now($deltime=0)
{
    return date("Y-m-d H:i:s", time()+$deltime);
}



//-----------------------------------------------------------------------------
///FSO 相关
//获取临时文件夹
function get_tempdir($temp_dir=""){
    if($temp_dir && is_dir($temp_dir) && is_readable($temp_dir)){
    
    }else{
        $temp_dir = ini_get('upload_tmp_dir');
        if ($temp_dir && (!is_dir($temp_dir) || !is_readable($temp_dir))) {
            $temp_dir = '';
        }
        if (!$temp_dir && function_exists('sys_get_temp_dir')) { 
            $temp_dir = sys_get_temp_dir();
        }
        $open_basedir = ini_get('open_basedir');
        if ($open_basedir) {
            $temp_dir     = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $temp_dir);
            $open_basedir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $open_basedir);
            if (substr($temp_dir, -1, 1) != DIRECTORY_SEPARATOR) {
                $temp_dir .= DIRECTORY_SEPARATOR;
            }
            $found_valid_tempdir = false;
            $open_basedirs = explode(PATH_SEPARATOR, $open_basedir);
            foreach ($open_basedirs as $basedir) {
                if (substr($basedir, -1, 1) != DIRECTORY_SEPARATOR) {
                    $basedir .= DIRECTORY_SEPARATOR;
                }
                if (preg_match('#^'.preg_quote($basedir).'#', $temp_dir)) {
                    $found_valid_tempdir = true;
                    break;
                }
            }
            if (!$found_valid_tempdir) {
                $temp_dir = '';
            }
            unset($open_basedirs, $found_valid_tempdir, $basedir);
        }
    }
    $temp_dir = @realpath($temp_dir); 
    //修正
    if($temp_dir=="C:\Windows"){
        $temp_dir="C:\Windows\Temp";
    }
    if (substr($temp_dir, -1, 1) != DIRECTORY_SEPARATOR) {
        $temp_dir .= DIRECTORY_SEPARATOR;
    }
    return $temp_dir;
}
///创建文件夹 mkdir ($dir,0777);
function make_dir($folder)
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

function getFilePath($f)
{
    $a=explode("/", $f);
    $p="";
    for ($i=0;$i<sizeof($a)-1;$i++) {
        $p.=$a[$i]."/";
    }
    return $p;
}

///创建文件
function make_file($filePath, $Content)
{
    if (!file_exists($filePath)) {
        //1.是否存在 / ,如果有，判断最后一个是否有小数点. 排除文件名后新建目录
        if (strpos($filePath, "/")>0) {
            $fileArr= preg_split("/\//", $filePath);
            $fileName=$fileArr[sizeof($fileArr)-1];
            if (strpos($fileName, ".")>0) {
                $filePath2=str_replace($fileName, "", $filePath);
                make_dir($filePath2);
            }
        }
        //file_put_contents($filePath, $Content);
        $handle = fopen($filePath, "a");
        if (!$handle) {
            return false;
        }
        if (fwrite($handle, $Content) == false) {
            return false;
            ;
        }
        fclose($handle);
        return true;
    }
}
function _cache($f, $content = null)
{
    if ($content) {
        if (is_array($content)) {
            $content = json_encode($content);
        }
        return file_put_contents($f, $content);
    } else {
        if (file_exists($f)) {
            return file_get_contents($f);
        } else {
            return false;
        }
    }
}


//XSS过滤函数
function RemoveXSS($val)
{
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);

        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
    }

 
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}


function isPhone()
{
    $ua = strtolower(@$_SERVER['HTTP_USER_AGENT']);
    $uachar = "/(nokia|iphone|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";
    return !preg_match("/(ipad)/i", $ua) && $ua != '' &&  preg_match($uachar, $ua);
}

/*
网页请求类
*/
function http($url, $data='', $headers=array(), $timeout = 30)
{
    $ch=curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //以上为简略版本


    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //$headers[]="User-Agent: ATUPHP(alextu.com)";
    if (count($headers) >= 1) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    //curl_setopt($ch, CURLOPT_HEADER, true);
    

    $response=curl_exec($ch);

    curl_close($ch);
    return $response;
}

function httpcode($url, $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    $data = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_code;
}

/**
 * 编码格式转换
 */
function utf162utf8($utf16)
{
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
    }
    $bytes = (ord($utf16[0]) << 8) | ord($utf16[1]);
    switch (true) {
        case ((0x7F & $bytes) == $bytes):
            // this case should never be reached, because we are in ASCII range
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return chr(0x7F & $bytes);
        case (0x07FF & $bytes) == $bytes:
            // return a 2-byte UTF-8 character
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return chr(0xC0 | (($bytes >> 6) & 0x1F))
                 . chr(0x80 | ($bytes & 0x3F));
        case (0xFFFF & $bytes) == $bytes:
            // return a 3-byte UTF-8 character
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return chr(0xE0 | (($bytes >> 12) & 0x0F))
                 . chr(0x80 | (($bytes >> 6) & 0x3F))
                 . chr(0x80 | ($bytes & 0x3F));
    }
    // ignoring UTF-32 for now, sorry
    return '';
}
function utf82utf16($utf8)
{
    // oh please oh please oh please oh please oh please
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
    }
    switch (strlen($utf8)) {
        case 1:
            // this case should never be reached, because we are in ASCII range
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return $utf8;
        case 2:
            // return a UTF-16 character from a 2-byte UTF-8 char
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return chr(0x07 & (ord($utf8[0]) >> 2))
                 . chr((0xC0 & (ord($utf8[0]) << 6))
                     | (0x3F & ord($utf8[1])));
        case 3:
            // return a UTF-16 character from a 3-byte UTF-8 char
            // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
            return chr((0xF0 & (ord($utf8[0]) << 4))
                     | (0x0F & (ord($utf8[1]) >> 2)))
                 . chr((0xC0 & (ord($utf8[1]) << 6))
                     | (0x7F & ord($utf8[2])));
    }
    // ignoring UTF-32 for now, sorry
    return '';
}
/**
 *   将数组转换为xml
 *   @param array $data    要转换的数组
 *   @param bool $root     是否要根节点
 *   @return string         xml字符串
 */
function arr2xml($data, $root = true)
{
    $str="";
    if ($root) {
        $str .= "<xml>";
    }
    foreach ($data as $key => $val) {
        //去掉key中的下标[]
        $key = preg_replace('/\[\d*\]/', '', $key);
        if (is_array($val)) {
            $child = arr2xml($val, false);
            $str .= "<$key>$child</$key>";
        } else {
            $str.= "<$key><![CDATA[$val]]></$key>";
        }
    }
    if ($root) {
        $str .= "</xml>";
    }
    return $str;
}
function xml2arr($xml)
{
    libxml_disable_entity_loader(true);
    $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $arr;
}

