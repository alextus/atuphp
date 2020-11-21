<?php
function str_len($str){
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
    if ($length){
        return strlen($str) - $length + intval($length / 3) * 2;
    }else{
        return strlen($str);
    }
}
function Str_left($String,$Length,$Append = false) {  
	//替换图片 
	//$String = preg_replace("/(<img.*>)/",'', $String);   
	//$String = preg_replace("/(<IMG.*>)/",'', $String); 

	if ($Length == 0 || strlen($String) <= $Length )   {   
		return $String;   
	}else{   
		if (function_exists('mb_substr')){
			$newstr = mb_substr($String, 0, $Length, "utf-8");
		}elseif (function_exists('iconv_substr')){
			$newstr = iconv_substr($String, 0, $Length, "utf-8");
		}else {
			$newstr = substr($String, 0, $Length);
		}
		if($Append){   $newstr .= "...";   }   
		return $newstr;   
	}   
}

function str2htm($txt){

	if(is_null($txt)){$txt="";}

	$txt = str_replace("&","&amp;",$txt);
	$txt = str_replace("<","&lt;",$txt);
	$txt = str_replace(">","&gt;",$txt);
	$txt = str_replace("'","‘",$txt);
	
	
	$txt = str_replace(" ","&nbsp;",$txt);
	$txt = str_replace("","&nbsp;&nbsp;",$txt);
	$txt = str_replace("/n","<br/>",$txt);
	$txt = str_replace(chr(13),"<br/>",$txt);
	$txt = str_replace(chr(10),"",$txt);
	$txt = str_replace(chr(0),"&nbsp;",$txt);
	$txt = str_replace(chr(7),"&nbsp;",$txt);
	$txt = str_replace(chr(9),"&nbsp;",$txt);
	$txt = str_replace(chr(11),"&nbsp;",$txt);
	$txt = str_replace(chr(12),"&nbsp;",$txt);
	$txt = str_replace(chr(255),"&nbsp;",$txt);
	
	return $txt;
}

function htm2str($txt){

	$txt = str_replace("&nbsp;",chr(0),$txt);
	$txt = str_replace("",chr(10),$txt);
	$txt = str_replace("<br/>","/n",$txt);
	$txt = str_replace("<br/>",chr(13),$txt);
	$txt = str_replace("&nbsp;&nbsp;","",$txt);
	$txt = str_replace("&nbsp;"," ",$txt);
	$txt = str_replace("‘","'",$txt);
	$txt = str_replace("&gt;",">",$txt);
	$txt = str_replace("&lt;","<",$txt);
	$txt = str_replace("&amp;","&",$txt);
	
	return $txt;
}

function strToArray($strs) { 
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








function md5_16($str){
	return substr(md5($str),8,16);
}

function random($length) { 
	$hash = ''; 
	$chars='123456789alextuiyn';
	$max = strlen($chars) - 1; 

	for($i = 0; $i < $length; $i++) { 
		$hash .= $chars[mt_rand(0, $max)]; 
	} 
	return $hash; 
} 

//---------------------------------------------------
//////////常用变量类型判断
function isNum($str){
	if(is_numeric($str)){return true;}else{return false; }
}

/*** 验证输入的邮件地址是否合法*/
function isEmail($str)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($str, '@') !== false && strpos($str, '.') !== false) {
        if (preg_match($chars, $str)){  return true; }else{  return false; }
    }else{
        return false;
    }
}
/*** 验证输入的手机是否合法*/
function isMobile($str)
{
    $chars = "/^0{0,1}(13[0-9]|14(0|1|5|6|7|9)|15([0-3]|[5-9])|(17([0-3]|[5-9]))|(18[0-9]))+\d{8}$/i";
 
    if (preg_match($chars, $str)){  return true; }else{  return false; }
   
}

function url(){
	return 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];

}
/*** 检查是否为一个合法的时间格式*/
function isTime($str){
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
    return preg_match($pattern, $str);
}

//---------------------------------------------------------------------------
///IP相关
/**** 获得用户的真实IP地址*/
function ip(){
    static $realip = NULL;
    if ($realip !== NULL){   return $realip;}
    if (isset($_SERVER)){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip){ $ip = trim($ip); if ($ip != 'unknown') { $realip = $ip; break;  }}} /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])){   $realip = $_SERVER['HTTP_CLIENT_IP'];}
		else{ if (isset($_SERVER['REMOTE_ADDR'])){ $realip = $_SERVER['REMOTE_ADDR'];}else{  $realip = '0.0.0.0'; }}
    }else{
        if (getenv('HTTP_X_FORWARDED_FOR')){   $realip = getenv('HTTP_X_FORWARDED_FOR');}
		elseif (getenv('HTTP_CLIENT_IP'))  {   $realip = getenv('HTTP_CLIENT_IP');      }
		else                               {   $realip = getenv('REMOTE_ADDR');         }
    }
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}
/*** 获取服务器的ip**/
function server_ip(){
    static $serverip = NULL;
    if ($serverip !== NULL){   return $serverip; }
    if (isset($_SERVER)){
        if (isset($_SERVER['SERVER_ADDR'])){  $serverip = $_SERVER['SERVER_ADDR'];}else{$serverip = '0.0.0.0'; }
    }else{
        $serverip = getenv('SERVER_ADDR');
    }
    return $serverip;
}

//-------------------------------------------------------------------------------------
////时间 相关

function Alexdate(){
	return date("Ymd", time());
}
function AlexDay(){return Alexdate();}
function AlexYm(){return date("Ym", time());}




function getMillisecond() {
	list($t1, $t2) = explode(' ', microtime());
	return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

function FormatNum($num,$weishu){
	$FormatNum=$num;
	
	for($i=strlen($num);$i<$weishu;$i++){$FormatNum="0".$FormatNum;} 
	return $FormatNum;
}

function Alextime(){
	return  time()-strtotime("1982-12-19");
}

function now(){ return date("Y-m-d H:i:s", time());}





//-----------------------------------------------------------------------------
///FSO 相关
///创建文件夹 mkdir ($dir,0777);
function make_dir($folder)
{
    $reval = false;
    if (!file_exists($folder))
    {
        @umask(0);                                         //如果目录不存在则尝试创建该目录
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);  //将目录路径拆分成数组
        $base = ($atmp[0][0] == '/') ? '/' : '';           //如果第一个字符为/则当作物理路径处理

        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] AS $val)
        {
            if ('' != $val){
                $base .= $val;
                if ('..' == $val || '.' == $val) { 
                    $base .= '/'; continue;               //如果目录为.或者..则直接补/继续下一个循环 
				} 
            }else{
                continue;
            }
            $base .= '/';
            if (!file_exists($base)){
                if (@mkdir(rtrim($base, '/'), 0777)){
                    @chmod($base, 0777); $reval = true;   //尝试创建目录，如果创建失败则继续循环 
                }
            }
        }
    }else{
        $reval = is_dir($folder);   //路径已经存在。返回该路径是不是一个目录 
    }
    clearstatcache();
    return $reval;
}

function getFilePath($f){
		 $a=explode("/",$f);
		 $p="";
		 for($i=0;$i<sizeof($a)-1;$i++){
			 $p.=$a[$i]."/";
		 }
		 return $p;
}
///删除一个目录，包括它的内容。 unlink($sFile);
function destroyDir($dir, $virtual = false)
{
	$ds = DIRECTORY_SEPARATOR;
	$dir = $virtual ? realpath($dir) : $dir;
	$dir = substr($dir, -1) == $ds ? substr($dir, 0, -1) : $dir;
	if (is_dir($dir) && $handle = opendir($dir)){
		while ($file = readdir($handle))
		{
			if ($file == '.' || $file == '..'){
				continue;
			}elseif (is_dir($dir.$ds.$file)){
				destroyDir($dir.$ds.$file);
			}else{
				unlink($dir.$ds.$file);
			}
		}
		closedir($handle);
		rmdir($dir);
		return true;
	}else{
		return false;
	}
} 
///创建文件
function make_file($filePath,$Content){	


	if(!file_exists($filePath)){
		//1.是否存在 / ,如果有，判断最后一个是否有小数点. 排除文件名后新建目录
		if(strpos($filePath,"/")>0){
			$fileArr= preg_split("/\//",$filePath);
			$fileName=$fileArr[sizeof($fileArr)-1];
			if(strpos($fileName,".")>0){
				$filePath2=str_replace($fileName,"",$filePath);
				make_dir($filePath2);
			}
		}
		//file_put_contents($filePath, $Content);
		$handle = fopen($filePath,"a"); 
		if(!$handle){ 
			return false;
		}
		if(fwrite($handle,$Content) == false){ 
			return false;; 
		} 
		fclose($handle); 
		return true;
	} 
}
//删除文件
function del_file($sFile){

    if(file_exists($sFile)){
		unlink($sFile);
	}
}

//XSS过滤函数
function RemoveXSS($val) {  

   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  

   $search = 'abcdefghijklmnopqrstuvwxyz'; 
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
   $search .= '1234567890!@#$%^&*()'; 
   $search .= '~`";:?+/={}[]-_|\'\\'; 
   for ($i = 0; $i < strlen($search); $i++) { 
	 
	  $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);

	  $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); 
   } 

 
   $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
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


function isPhone(){
  $ua = strtolower(@$_SERVER['HTTP_USER_AGENT']);
  $uachar = "/(nokia|iphone|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";
  return !preg_match("/(ipad)/i", $ua) && $ua != '' &&  preg_match($uachar, $ua); 
}

