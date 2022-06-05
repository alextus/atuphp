<?php
define('ATU_VERSION', '1.0.0');

function &get_instance()
{
    return ATU_Controller::get_instance();
}

//以下代码需要精减

/**
 * ATUphp
 *
 * 是我的一个开源PHP框架，参考CI，精减了许多同能，又加强了一些功能
 */

if (! function_exists('is_php')) {
    function is_php($version = '5.0.0')
    {
        static $_is_php;
        $version = (string)$version;

        if (! isset($_is_php[$version])) {
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? false : true;
        }

        return $_is_php[$version];
    }
}


// ------------------------------------------------------------------------

/**
* Class registry
*
* This function acts as a singleton.  If the requested class does not
* exist it is instantiated and set to a static variable.  If it has
* previously been instantiated the variable is returned.
*
* @access	public
* @param	string	the class name being requested
* @param	string	the directory where the class should be found
* @param	string	the class name prefix
* @return	object
*/
if (! function_exists('load_class')) {
    function &load_class($class, $directory = 'libraries', $prefix = 'ATU_')
    {
        static $_classes = array();

        // Does the class exist?  If so, we're done...
        if (isset($_classes[$class])) {
            return $_classes[$class];
        }
        $name = false;

        // Look for the class first in the local application/libraries folder
        // then in the native system/libraries folder
        foreach (array(APPPATH, BASEPATH) as $path) {
            if (file_exists($path.$directory.'/'.$class.'.php')) {
                $name = $prefix.$class;
                
                
                if (class_exists($name) === false) {
                    require($path.$directory.'/'.$class.'.php');
                }

                break;
            }
        }
        // Did we find the class?
        if ($name === false) {
            // Note: We use exit() rather then show_error() in order to avoid a
            // self-referencing loop with the Excptions class
            exit('Unable to locate the specified class: '.$class.'.php');
        }

        // Keep track of what we just loaded
        is_loaded($class);

        $_classes[$class] = new $name();
        return $_classes[$class];
    }
}

// --------------------------------------------------------------------

/**
* Keeps track of which libraries have been loaded.  This function is
* called by the load_class() function above
*
* @access	public
* @return	array
*/
if (! function_exists('is_loaded')) {
    function &is_loaded($class = '')
    {
        static $_is_loaded = array();

        if ($class != '') {
            $_is_loaded[strtolower($class)] = $class;
        }

        return $_is_loaded;
    }
}

// ------------------------------------------------------------------------

/**
* Loads the main config.php file
*
* This function lets us grab the config file even if the Config class
* hasn't been instantiated yet
*
* @access	private
* @return	array
*/
if (! function_exists('get_config')) {
    function &get_config($replace = array())
    {
        static $_config;
        
        if (isset($_config)) {
            return $_config[0];
        }

        
        $file_path = APPPATH.'config.php';
        

        // Fetch the config file
        if (! file_exists($file_path)) {
            //exit('The configuration file does not exist.');
            $config= array();
        }else{
            require($file_path);
        }


        

        // Does the $config array exist in the file?
        if (! isset($config) or ! is_array($config)) {
            exit('Your config file does not appear to be formatted correctly.');
        }

        // Are any values being dynamically replaced?
        if (count($replace) > 0) {
            foreach ($replace as $key => $val) {
                if (isset($config[$key])) {
                    $config[$key] = $val;
                }
            }
        }

        $_config[0] =& $config;
        return $_config[0];
    }
}

// ------------------------------------------------------------------------

/**
* Returns the specified config item
*
* @access	public
* @return	mixed
*/
if (! function_exists('config_item')) {
    function config_item($item)
    {
        static $_config_item = array();

        if (! isset($_config_item[$item])) {
            $config =& get_config();

            if (! isset($config[$item])) {
                return false;
            }
            $_config_item[$item] = $config[$item];
        }

        return $_config_item[$item];
    }
}

// ------------------------------------------------------------------------

/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
if (! function_exists('show_error')) {
    function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
    {
        $_error =& load_class('Exceptions', 'core');
        echo $_error->show_error($heading, $message, 'error_general', $status_code);
        exit;
    }
}

// ------------------------------------------------------------------------

/**
* 404 Page Handler
*
* This function is similar to the show_error() function above
* However, instead of the standard error template it displays
* 404 errors.
*
* @access	public
* @return	void
*/
if (! function_exists('show_404')) {
    function show_404($page = '', $arr=array(), $log_error = true)
    {
        $_error =& load_class('Exceptions', 'core');
        $_error->show_404($page, $arr, $log_error);
        exit;
    }
}

// ------------------------------------------------------------------------

/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
if (! function_exists('log_message')) { 
    function log_message($type, $message, $file="")
    {
       
        if ((!config_item("need_debug") && $type=="debug")|| !config_item("log_path")) {
            return;
        }
   
        $logPath=config_item("log_path");
        $filefix=$type."_";
        if(!file_exists($logPath)){
            echo 'log folder not exist';
            exit;
        }
        
        $needType=$file?true:false;
        $file=$logPath.($file?$file:$filefix.date("Ymd", time()).".txt");
      
        $content=date("H:i:s", time()).":".($needType?$type.":":"").$message."\n";
       // echo $file."|";
        $fps = fopen($file, "a+");
        fwrite($fps, $content);
        fclose($fps);
        
    }
}

// ------------------------------------------------------------------------

/**
 * Set HTTP Status Header
 *
 * @access	public
 * @param	int		the status code
 * @param	string
 * @return	void
 */
if (! function_exists('set_status_header')) {
    function set_status_header($code = 200, $text = '')
    {
        $stati = array(
                            200	=> 'OK',
                            201	=> 'Created',
                            202	=> 'Accepted',
                            203	=> 'Non-Authoritative Information',
                            204	=> 'No Content',
                            205	=> 'Reset Content',
                            206	=> 'Partial Content',

                            300	=> 'Multiple Choices',
                            301	=> 'Moved Permanently',
                            302	=> 'Found',
                            304	=> 'Not Modified',
                            305	=> 'Use Proxy',
                            307	=> 'Temporary Redirect',

                            400	=> 'Bad Request',
                            401	=> 'Unauthorized',
                            403	=> 'Forbidden',
                            404	=> 'Not Found',
                            405	=> 'Method Not Allowed',
                            406	=> 'Not Acceptable',
                            407	=> 'Proxy Authentication Required',
                            408	=> 'Request Timeout',
                            409	=> 'Conflict',
                            410	=> 'Gone',
                            411	=> 'Length Required',
                            412	=> 'Precondition Failed',
                            413	=> 'Request Entity Too Large',
                            414	=> 'Request-URI Too Long',
                            415	=> 'Unsupported Media Type',
                            416	=> 'Requested Range Not Satisfiable',
                            417	=> 'Expectation Failed',

                            500	=> 'Internal Server Error',
                            501	=> 'Not Implemented',
                            502	=> 'Bad Gateway',
                            503	=> 'Service Unavailable',
                            504	=> 'Gateway Timeout',
                            505	=> 'HTTP Version Not Supported'
                        );

        if ($code == '' or ! is_numeric($code)) {
            show_error('Status codes must be numeric', 500);
        }

        if (isset($stati[$code]) and $text == '') {
            $text = $stati[$code];
        }

        if ($text == '') {
            show_error('No status text available.  Please check your status code number or supply your own message text.', 500);
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;

        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
            header("Status: {$code} {$text}", true);
        } elseif ($server_protocol == 'HTTP/1.1' or $server_protocol == 'HTTP/1.0') {
            header($server_protocol." {$code} {$text}", true, $code);
        } else {
            header("HTTP/1.1 {$code} {$text}", true, $code);
        }
    }
}

// --------------------------------------------------------------------

/**
 * Remove Invisible Characters
 *
 * This prevents sandwiching null characters
 * between ascii characters, like Java\0script.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if (! function_exists('remove_invisible_characters')) {
    function remove_invisible_characters($str, $url_encoded = true)
    {
        $non_displayables = array();
        
        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)
        
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
        }
        
        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }
}

// ------------------------------------------------------------------------

/**
* Returns HTML escaped variable
*
* @access	public
* @param	mixed
* @return	mixed
*/
if (! function_exists('html_escape')) {
    function html_escape($var)
    {
        if (is_array($var)) {
            return array_map('html_escape', $var);
        } else {
            return htmlspecialchars($var, ENT_QUOTES, config_item('charset'));
        }
    }
}


$dir=dirname(__FILE__)."/";

require_once $dir.'Model.php';
require_once $dir.'Controller.php';

//兼容低版本PHP
require_once $dir."../fun/function_exists.php";
//常用函数
require_once $dir."../fun/common.php";

//ATU_Mysql
require_once $dir.'../libraries/Mysql.php';
//ATU_Http
require_once $dir."../libraries/Http.php";
//ATU_File
require_once $dir."../libraries/File.php";
//ATU_Smarty
require_once $dir."../libraries/Smarty.php";

//model加载机制
function loadModel($model)
{
    include_once  APPPATH . 'models/' . $model . 'Model.php';
}
//model加载机制
function loadLibrary($library)
{
    include_once  APPPATH . 'libraries/' . $library . '.php';
}
//缓存机制
function cache($f, $content = null)
{
    $f=str_replace('data/cache/','',$f);
    $f = APPPATH . 'data/cache/' . $f;
    if(strpos($f,".")==false){
        $f.='.json';
    }
    return _cache($f, $content);
}
