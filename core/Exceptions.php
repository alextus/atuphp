<?php

/**
 * ATUphp
 * Exceptions Class
 * 错误处理机制
 */

class ATU_Exceptions
{
	var $action;
	var $severity;
	var $message;
	var $filename;
	var $line;

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var int
	 * @access public
	 */
	var $ob_level;

	/**
	 * List if available error levels
	 *
	 * @var array
	 * @access public
	 */
	var $levels = array(
		E_ERROR				=>	'Error',
		E_WARNING			=>	'Warning',
		E_PARSE				=>	'Parsing Error',
		E_NOTICE			=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT			=>	'Runtime Notice'
	);


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ob_level = ob_get_level();
		// Note:  Do not log messages from this constructor.
	}

	// --------------------------------------------------------------------

	/**
	 * Exception Logger
	 *
	 * This function logs PHP generated error messages
	 *
	 * @access	private
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	function log_exception($severity, $message, $filepath, $line)
	{
		$severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		log_message('error', 'Severity: ' . $severity . '  --> ' . $message . ' ' . $filepath . ' ' . $line, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * 404 Page Not Found Handler
	 *
	 * @access	private
	 * @param	string	the page
	 * @param 	bool	log error yes/no
	 * @return	string
	 */
	function show_404($page = '', $arr = array(), $log_error = TRUE)
	{

		$heading = "404 Page Not Found";
		$message = "The page you requested was not found.";

		// By default we log this, but allow a dev to skip it
			$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		
			$urlArr=preg_split("/index.php\//",$url);
		
			if(isset($urlArr[1]) && !empty($arr[$urlArr[1]])){
				header("Location: ".$urlArr[0].$arr[$urlArr[1]]); 

				
			}else{
				if ($log_error){
				
					$url=str_replace("/index.php/","/",$url);
					if(!empty($_SERVER['QUERY_STRING'])){
						$url.='?'.$_SERVER['QUERY_STRING'];
					}
					
					if(!empty($_SERVER['HTTP_REFERER'])){$url.=":".$_SERVER['HTTP_REFERER'];}
					
					log_message('error', '404 Page Not Found --> '.$page.":".$url);
				}
				echo $this->show_error($heading, $message, 'error_404', 404);
		
			}
			exit;
		}




	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @param 	int		the status code
	 * @return	string
	 */
	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);

		$message = '<p>' . implode('</p><p>', (!is_array($message)) ? array($message) : $message) . '</p>';
		echo  $message;
		exit;
		if (ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include(APPPATH . 'view/errors/' . $template . '.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	// --------------------------------------------------------------------

	/**
	 * Native PHP error handler
	 *
	 * @access	private
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		$filepath = str_replace("\\", "/", $filepath);

		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/')) {
			$x = explode('/', $filepath);
			$filepath = $x[count($x) - 2] . '/' . end($x);
		}

		if (ob_get_level() > $this->ob_level + 1) {
			ob_end_flush();
		}
		ob_start();
		include(BASEPATH . 'errors/error_php.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}
// END Exceptions Class

/* End of file Exceptions.php */
/* Location: ./system/core/Exceptions.php */