<?php
/**
* ATU_Http HTTP请求工具
* 支持以下功能：
* 1：支持ssl连接和proxy代理连接
* 2: 对cookie的自动支持
* 3: 简单的GET/POST常规操作
* 4: 支持单个文件上传或同字段的多文件上传,支持相对路径或绝对路径.
* 5: 支持返回发送请求前和请求后所有的服务器信息和服务器Header信息
* 6: 自动支持lighttpd服务器
* 7: 支持自动设置 REFERER 引用页
* 8: 自动支持服务器301跳转或重写问题(谢谢郑GG)
* 9: 其它可选项,如自定义端口，超时时间，USERAGENT，Gzip压缩等.
*/
class ATU_Http
{
    //CURL句柄
    private static $ch=null;
    //CURL执行前后所设置或服务器端返回的信息
    private static $info=array();
    //CURL SETOPT 信息
    private static $setopt=array(
        //访问的端口,http默认是 80
        'port'=>80,
        //客户端 USERAGENT,如:"Mozilla/4.0",为空则使用用户的浏览器
        'userAgent'=>'',
        //连接超时时间
        'timeOut'=>30,
        //是否使用 COOKIE 建议打开，因为一般网站都会用到
        'useCookie'=>true,
        //是否支持SSL
        'ssl'=>false,
        //客户端是否支持 gzip压缩
        'gzip'=>true,
        //是否使用代理
        'proxy'=>false,
        //代理类型,可选择 HTTP 或 SOCKS5
        'proxyType'=>'HTTP',
        //代理的主机地址,如果是 HTTP 方式则要写成URL形式如:"http://www.proxy.com"
        //SOCKS5 方式则直接写主机域名为IP的形式，如:"192.168.1.1"
        'proxyHost'=>'192.168.1.1',
        //代理主机的端口
        'proxyPort'=>1234,
        //代理是否要身份认证(HTTP方式时)
        'proxyAuth'=>false,
        //认证的方式.可选择 BASIC 或 NTLM 方式
        'proxyAuthType'=>'BASIC',
        //认证的用户名和密码
        'proxyAuthUser'=>'user',
        'proxyAuthPwd'=>'password',
    );

    /**
    * 构造函数
    *
    * @param array $setopt :请参考 private $setopt 来设置
    */
    public function __construct($setopt=array())
    {
        //合并用户的设置和系统的默认设置
        self::$setopt =array_merge(self::$setopt, $setopt);
    }
    /**
    * 以 GET 方式执行请求
    *
    * @param string $url :请求的URL
    * @param array $params ：请求的参数,格式如: array('id'=>10,'name'=>'zhangsan')
    * @param array $referer :引用页面,为空时自动设置,如果服务器有对这个控制的话则一定要设置的.
    * @return 错误返回:false 正确返回:结果内容
    */
    public static function get($url, $params=array(), $referer='')
    {
        return self::_request('GET', $url, $params, array(), $referer);
    }

    /**
    * 以 POST 方式执行请求
    *
    * @param string $url :请求的URL
    * @param array $params ：请求的参数,格式如: array('id'=>10,'name'=>'yuanwei')
    * @param array $uploadFile :上传的文件,支持相对路径,格式如下
    * 单个文件上传:array('img1'=>'./file/a.jpg')
    * 同字段多个文件上传:array('img'=>array('./file/a.jpg','./file/b.jpg'))
    * @param array $referer :引用页面,引用页面,为空时自动设置,如果服务器有对这个控制的话则一定要设置的.
    * @return 错误返回:false 正确返回:结果内容
    */
    public static function post($url, $params=array(), $uploadFile=array(), $referer='')
    {
        return self::_request('POST', $url, $params, $uploadFile, $referer);
    }

    /**
    * 得到错误信息
    *
    * @return string
    */
    public static function error()
    {
        return curl_error(self::$ch);
    }

    /**
    * 得到错误代码
    *
    * @return int
    */
    public static function errno()
    {
        return curl_errno(self::$ch);
    }

    /**
    * 得到发送请求前和请求后所有的服务器信息和服务器Header信息,其中
    * [before] ：请求前所设置的信息
    * [after] :请求后所有的服务器信息
    * [header] :服务器Header报文信息
    *
    * @return array
    */
    public static function getInfo()
    {
        return self::$info;
    }

    /**
    * 析构函数
    *
    */
    public function __destruct()
    {
        //关闭CURL
        if (self::$ch) {
            curl_close(self::$ch);
        }
    }

    public static function close()
    {
        if (self::$ch) {
            curl_close(self::$ch);
        }
    }
    private static function getFile($path)
    {
        if (class_exists('\CURLFile')) {
            return  new \CURLFile(realpath($path));//>=5.5
        } else {
            return   '@' . realpath($path);//<=5.5
        }
    }
    /**
    * 私有方法:执行最终请求
    *
    * @param string $method :HTTP请求方式
    * @param string $url :请求的URL
    * @param array $params ：请求的参数
    * @param array $uploadFile :上传的文件(只有POST时才生效)
    * @param array $referer :引用页面
    * @return 错误返回:false 正确返回:结果内容
    */
    private static function _request($method, $url, $params=array(), $uploadFile=array(), $referer='')
    {
        self::_init();

        //如果是以GET方式请求则要连接到URL后面
        if ($method=='GET') {
            $url=self::_parseUrl($url, $params);
        }
        //设置请求的URL
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        //如果是POST
        if ($method=='POST') {
            //发送一个常规的POST请求，类型为：application/x-www-form-urlencoded
            curl_setopt(self::$ch, CURLOPT_POST, true) ;
            //设置POST字段值
            $postData=self::_parsmEncode($params, false);
            //如果有上传文件
            if ($uploadFile) {
                foreach ($uploadFile as $key=>$file) {
                    if (is_array($file)) {
                        $n=0;
                        foreach ($file as $f) {
                            //文件必需是绝对路径
                            $postData[$key.'['.$n++.']'] =self::etFile($f);
                        }
                    } else {
                        $postData[$key] =self::getFile($file);
                    }
                }
            }
            //pr($postData); die;
            if (class_exists('\CURLFile')) {
                curl_setopt(self::$ch, CURLOPT_SAFE_UPLOAD, true);
            } else {
                if (defined('CURLOPT_SAFE_UPLOAD')) {
                    curl_setopt(self::$ch, CURLOPT_SAFE_UPLOAD, false);
                }
            }
            curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $postData);
        }
        //设置了引用页,否则自动设置
        if ($referer) {
            curl_setopt(self::$ch, CURLOPT_REFERER, $referer);
        } else {
            curl_setopt(self::$ch, CURLOPT_AUTOREFERER, true);
        }
        //得到所有设置的信息
        self::$info['before'] = curl_getinfo(self::$ch);
        //开始执行请求
        $result= curl_exec(self::$ch);
        //得到报文头
        $headerSize= curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE);
        self::$info['header'] =substr($result, 0, $headerSize);
        //去掉报文头
        $result=substr($result, $headerSize);
        //得到所有包括服务器返回的信息
        self::$info['after'] = curl_getinfo(self::$ch);
        //如果请求成功
        if (self::errno() ==0) { //&& $this->info['after']['http_code'] == 200
            return $result;
        } else {
            return false;
        }
    }
    /**
    * 返回解析后的URL，GET方式时会用到
    *
    * @param string $url :URL
    * @param array $params :加在URL后的参数
    * @return string
    */
    private static function _parseUrl($url, $params)
    {
        $fieldStr=self::_parsmEncode($params);
        if ($fieldStr) {
            $url.=strstr($url, '?')===false?'?':'&';
            $url.=$fieldStr;
        }
        return $url;
    }

    /**
    * 对参数进行ENCODE编码
    *
    * @param array $params :参数
    * @param bool $isRetStr : true：以字符串返回 false:以数组返回
    * @return string || array
    */
    private static function _parsmEncode($params, $isRetStr=true)
    {
        $fieldStr='';
        $spr='';
        $result=array();
        foreach ($params as $key=>$value) {
            $value=urlencode($value);
            $fieldStr.=$spr.$key.'='.$value;
            $spr='&';
            $result[$key] =$value;
        }
        return $isRetStr?$fieldStr:$result;
    }

    private static function _init()
    {
        //如果没有安装CURL则终止程序
        function_exists('curl_init') ||die('CURL Library Not Loaded');
        //初始化
        self::$ch = curl_init();
        //设置CURL连接的端口
        curl_setopt(self::$ch, CURLOPT_PORT, self::$setopt['port']);
        //使用代理
        if (self::$setopt['proxy']) {
            $proxyType=self::$setopt['proxyType']=='HTTP'? CURLPROXY_HTTP : CURLPROXY_SOCKS5;
            curl_setopt(self::$ch, CURLOPT_PROXYTYPE, $proxyType);
            curl_setopt(self::$ch, CURLOPT_PROXY, self::$setopt['proxyHost']);
            curl_setopt(self::$ch, CURLOPT_PROXYPORT, self::$setopt['proxyPort']);
            //代理要认证
            if (self::$setopt['proxyAuth']) {
                $proxyAuthType=self::$setopt['proxyAuthType']=='BASIC'? CURLAUTH_BASIC : CURLAUTH_NTLM;
                curl_setopt(self::$ch, CURLOPT_PROXYAUTH, $proxyAuthType);
                $user="[".self::$setopt['proxyAuthUser']."]:[".self::$setopt['proxyAuthPwd']."]";
                curl_setopt(self::$ch, CURLOPT_PROXYUSERPWD, $user);
            }
        }
        //启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
        curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, true);
        //打开的支持SSL
        if (self::$setopt['ssl']) {
            //不对认证证书来源的检查
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, false);
            //从证书中检查SSL加密算法是否存在
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, true);
        }
        //设置http头,支持lighttpd服务器的访问
        $header[]='Expect:';
        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $header);
        //设置 HTTP USERAGENT
        $userAgent=self::$setopt['userAgent'] ?self::$setopt['userAgent'] :$_SERVER['HTTP_USER_AGENT'];
        curl_setopt(self::$ch, CURLOPT_USERAGENT, $userAgent);
        //设置连接等待时间,0不等待
        curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, self::$setopt['timeOut']);
        //设置curl允许执行的最长秒数
        curl_setopt(self::$ch, CURLOPT_TIMEOUT, self::$setopt['timeOut']);
        //设置客户端是否支持 gzip压缩
        if (self::$setopt['gzip']) {
            curl_setopt(self::$ch, CURLOPT_ENCODING, 'gzip');
        }
        //是否使用到COOKIE
        if (self::$setopt['useCookie']) {
            //生成存放临时COOKIE的文件(要绝对路径)
            $cookfile=tempnam(sys_get_temp_dir(), 'cuk');
            //连接关闭以后，存放cookie信息
            curl_setopt(self::$ch, CURLOPT_COOKIEJAR, $cookfile);
            curl_setopt(self::$ch, CURLOPT_COOKIEFILE, $cookfile);
        }
        //是否将头文件的信息作为数据流输出(HEADER信息),这里保留报文
        curl_setopt(self::$ch, CURLOPT_HEADER, true);
        //获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt(self::$ch, CURLOPT_BINARYTRANSFER, true) ;
    }
}
