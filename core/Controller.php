<?php

/**
 * Controller Class
 *
 * APP 父类
 */
class ATU_Controller
{
    private static $instance;
    public $marker = array();
    public $theme = array();

    public function __construct()
    {
        self::$instance = &$this;

        $this->https=new ATU_Http();
        $config = &get_config();
        $this->config = $config;
        foreach (is_loaded() as $var => $class) {
            $this->$var = &load_class($class);
        }




        $this->load = &load_class('Loader', 'core');
        $this->load->initialize(); //初始化加载
        //安全机制
        $this->Security = &load_class('Security', 'core');
        /*
        load_class 加载基础
        $this->load =& load_class('Loader', 'core');
        $this->load->initialize();
        */
        /*
        $this->smarty =& load_class('Smarty', 'core');
        $this->smarty->smarty_dir  = APPPATH.'view/';
        $this->smarty->compile_dir   = APPPATH.'view/temp';
        $this->smarty->force_compile = false;
        */

        log_message('debug', "Controller Class Initialized");
        /*
        $BM =& load_class('Benchmark', 'core');

        //header("Content-type: text/html; charset=utf-8");
        $this->base_url=$this->config->item("base_url");
        $this->imgPath =$this->config->item("img_url");

        //判断是在本地还是服务器
        $d=preg_split("/index.php/",current_url());
        $d2=preg_split("/\//",$d[0]);

        */
        $this->mark('_time_start');


        header("Content-Type: text/html; charset=UTF-8");
    }
    public static function &get_instance()
    {
        return self::$instance;
    }

    private function mark($name)
    {
        $this->marker[$name] = microtime();
    }

    //计算时间差
    public function elapsed_time($point1 = '_time_start', $point2 = '', $decimals = 6)
    {
        if ($point1 == '') {
            return '{elapsed_time}';
        }

        if (!isset($this->marker[$point1])) {
            return '';
        }

        if (!isset($this->marker[$point2])) {
            $this->marker[$point2] = microtime();
        }

        list($sm, $ss) = explode(' ', $this->marker[$point1]);
        list($em, $es) = explode(' ', $this->marker[$point2]);

        return number_format(($em + $es) - ($sm + $ss), $decimals);
    }

    public function memory_usage()
    {
        return round(memory_get_usage() / 1024 / 1024, 2) . 'MB';
    }


    
    public function set_cookie($name = '', $value = '', $expire = '', $path = '/', $domain = '', $prefix = '', $secure = false)
    {
        /*

        if (is_array($name))
        {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name') as $item)
            {
                if (isset($name[$item]))
                {
                    $$item = $name[$item];
                }
            }
        }
        if ($path == '/' && config_item('cookie_path') != '/')
        {
            $path = config_item('cookie_path');
        }
        if ($domain == '' && config_item('cookie_domain') != '')
        {
            $domain = config_item('cookie_domain');
        }

        if ($secure == FALSE && config_item('cookie_secure') != FALSE)
        {
            $secure = config_item('cookie_secure');
        }
        */
        if ($prefix == '' && config_item('cookie_prefix') != '') {
            $prefix = config_item('cookie_prefix');
        }

        if (!is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }


        setcookie($name, $value, $expire, $path, $domain, $secure);
    }

    public function _get($name,$xss=true)
    {
        return _get($name,$xss);
    }

    public function _post($name = "",$xss=true)
    {
        return _post($name,$xss);
    }

    public function _cookie($name, $value = null, $exitTime = 0)
    {
        $prefix = config_item('cookie_prefix') != '' ? config_item('cookie_prefix') : "";
        $name = $prefix . $name;

        if (is_null($value)) {
            return S_cookie($name);
        } else {
            $this->set_cookie($name, $value, $exitTime == 0 ? time() + 1 * 60 * 60 : $exitTime);
        }
    }
    public function get($name = "", $xss = true)
    {
        return S_get($name, $xss);
    }
    public function post($name = "", $xss = true)
    {
        return S_post($name, $xss);
    }
    public function request($name = "", $xss = true)
    {
        return request($name, $xss);
    }
    public function cookie($name, $value = null, $exitTime = 0)
    {
        return $this->_cookie($name, $value, $exitTime);
    }



    public function _setData($file, $data = false)
    {
        if ($data) {
            return file_put_contents(DataPATH . $file . ".json", json_encode($data));
        } else {
            return json_decode(file_get_contents(DataPATH . $file . ".json"), true);
        }
    }
    public function _getData($file)
    {
        return json_decode(file_get_contents(DataPATH . $file . ".json"), true);
    }
    private function mnip()
    {
        $ip_long = array(
            array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        return $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    }

    public function getUrlHtml($url, $moniIP=0)
    {
        $qingqiuip = $this->mnip();
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array('CLIENT-IP:' . $qingqiuip, 'X-FORWARDED-FOR:' . $qingqiuip);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        //curl_setopt($ch, CURLOPT_HEADER, 0); //是否抓取头部信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $q = curl_exec($ch);

        //print_r($header);
        if ($q == false) {
            //echo $url;
        
            return curl_error($ch);
        } else {
            return $q;
        }
    }
}
