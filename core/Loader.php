<?php  
/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package		ATUphp
 * @subpackage	Libraries
 * @author		EllisLab Dev Team
 * @category	Loader
 * @link		http://ATUphp.com/user_guide/libraries/loader.html
 */
class ATU_Loader {

	protected $_atu_ob_level;
	protected $_atu_view_paths		= array();
	protected $_atu_library_paths	= array();
	protected $_atu_model_paths		= array();
	protected $_atu_helper_paths	= array();
	protected $_base_classes		= array(); // Set by the controller class
	protected $_atu_cached_vars		= array();
	protected $_atu_classes			= array();
	protected $_atu_loaded_files	= array();
	protected $_atu_models			= array();
	protected $_atu_helpers			= array();
	protected $_atu_varmap			= array('unit_test' => 'unit',
											'user_agent' => 'agent');

	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 */
	public function __construct()
	{
		$this->_atu_ob_level  = ob_get_level();
		$this->_atu_library_paths = array(APPPATH, BASEPATH);
		$this->_atu_helper_paths = array(APPPATH, BASEPATH);
		$this->_atu_model_paths = array(APPPATH, BASEPATH);
		$this->_atu_view_paths = array(APPPATH.'views/'	=> TRUE);

		log_message('debug', "Loader Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the Loader
	 *
	 * This method is called once in ATU_Controller.
	 *
	 * @param 	array
	 * @return 	object
	 */
	public function initialize()
	{
		$this->_atu_classes = array();
		$this->_atu_loaded_files = array();
		$this->_atu_models = array();
		$this->_base_classes =& is_loaded();

		$this->_atu_autoloader();

		return $this;
	}
	
	//加载数据库
	public function database($dbs = NULL){
		$ATU =& get_instance();
		if (isset($ATU->db) AND is_object($ATU->db))
		{
			return FALSE;
		}
		require_once(BASEPATH.'core/Mysql.php');
		$ATU->db = '';
		
		// Load the DB class
		$ATU->db =new ATU_Mysql($dbs);
		
	}

	// --------------------------------------------------------------------

	/**
	 * Is Loaded
	 *
	 * A utility function to test if a class is in the self::$_atu_classes array.
	 * This function returns the object name if the class tested for is loaded,
	 * and returns FALSE if it isn't.
	 *
	 * It is mainly used in the form_helper -> _get_validation_object()
	 *
	 * @param 	string	class being checked for
	 * @return 	mixed	class object name on the CI SuperObject or FALSE
	 */
	public function is_loaded($class)
	{
		if (isset($this->_atu_classes[$class]))
		{
			return $this->_atu_classes[$class];
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	public function library($library = '', $params = NULL, $object_name = NULL)
	{
		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->library($class, $params);
			}

			return;
		}
	
		if ($library == '' OR isset($this->_base_classes[$library]))
		{
			
			return FALSE;
		}

		if ( ! is_null($params) && ! is_array($params))
		{
			
			$params = NULL;
		}

		$this->_atu_load_class($library, $params, $object_name);
	}

	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */
	public function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach ($model as $babe)
			{
				$this->model($babe);
			}
			return;
		}

		if ($model == '')
		{
			return;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, $last_slash + 1);

			// And the model name behind it
			$model = substr($model, $last_slash + 1);
		}

		if ($name == '')
		{
			$name = $model;
		}

		if (in_array($name, $this->_atu_models, TRUE))
		{
			return;
		}

		$ATU =& get_instance();
		if (isset($ATU->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		//$model = strtolower($model);

		foreach ($this->_atu_model_paths as $mod_path)
		{
			$model=str_replace("Model","",$model)."Model";
			if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
			{
				continue;
			}
			if ( ! class_exists('ATU_Model'))
			{
				load_class('Model', 'core');
			}
		
			require_once($mod_path.'models/'.$path.$model.'.php');

			//$model = ucfirst($model);

			$ATU->$name = new $model();

			$this->_atu_models[] = $name;
			
			return;
		}

		// couldn't find the model
		show_error('Unable to locate the model you have specified: '.$model);
	}

	
	// --------------------------------------------------------------------

	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_atu_load(array('_atu_view' => $view, '_atu_vars' => $this->_atu_object_to_array($vars), '_atu_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function file($path, $return = FALSE)
	{
		return $this->_atu_load(array('_atu_path' => $path, '_atu_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @param	array
	 * @param 	string
	 * @return	void
	 */
	public function vars($vars = array(), $val = '')
	{
		if ($val != '' AND is_string($vars))
		{
			$vars = array($vars => $val);
		}

		$vars = $this->_atu_object_to_array($vars);

		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_atu_cached_vars[$key] = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get Variable
	 *
	 * Check if a variable is set and retrieve it.
	 *
	 * @param	array
	 * @return	void
	 */
	public function get_var($key)
	{
		return isset($this->_atu_cached_vars[$key]) ? $this->_atu_cached_vars[$key] : NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 *
	 * @param	mixed
	 * @return	void
	 */
	public function helper($helpers = array())
	{
		foreach ($this->_atu_prep_filename($helpers, '_helper') as $helper)
		{
			if (isset($this->_atu_helpers[$helper]))
			{
				continue;
			}

			$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix').$helper.'.php';

			// Is this a helper extension request?
			if (file_exists($ext_helper))
			{
				$base_helper = BASEPATH.'helpers/'.$helper.'.php';

				if ( ! file_exists($base_helper))
				{
					show_error('Unable to load the requested file: helpers/'.$helper.'.php');
				}

				include_once($ext_helper);
				include_once($base_helper);

				$this->_atu_helpers[$helper] = TRUE;
				log_message('debug', 'Helper loaded: '.$helper);
				continue;
			}

			// Try to load the helper
			foreach ($this->_atu_helper_paths as $path)
			{
				if (file_exists($path.'helpers/'.$helper.'.php'))
				{
					include_once($path.'helpers/'.$helper.'.php');

					$this->_atu_helpers[$helper] = TRUE;
					log_message('debug', 'Helper loaded: '.$helper);
					break;
				}
			}

			// unable to load the helper
			if ( ! isset($this->_atu_helpers[$helper]))
			{
				show_error('Unable to load the requested file: helpers/'.$helper.'.php');
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @param	array
	 * @return	void
	 */
	public function helpers($helpers = array())
	{
		$this->helper($helpers);
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a config file
	 *
	 * @param	string
	 * @param	bool
	 * @param 	bool
	 * @return	void
	 */
	public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$ATU =& get_instance();
		$ATU->config->load($file, $use_sections, $fail_gracefully);
	}




	// --------------------------------------------------------------------

	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _atu_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @param	array
	 * @return	void
	 */
	protected function _atu_load($_atu_data)
	{
		// Set the default data variables
		foreach (array('_atu_view', '_atu_vars', '_atu_path', '_atu_return') as $_atu_val)
		{
			$$_atu_val = ( ! isset($_atu_data[$_atu_val])) ? FALSE : $_atu_data[$_atu_val];
		}

		$file_exists = FALSE;

		// Set the path to the requested file
		if ($_atu_path != '')
		{
			$_atu_x = explode('/', $_atu_path);
			$_atu_file = end($_atu_x);
		}
		else
		{
			$_atu_ext = pathinfo($_atu_view, PATHINFO_EXTENSION);
			$_atu_file = ($_atu_ext == '') ? $_atu_view.'.php' : $_atu_view;

			foreach ($this->_atu_view_paths as $view_file => $cascade)
			{
				if (file_exists($view_file.$_atu_file))
				{
					$_atu_path = $view_file.$_atu_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade)
				{
					break;
				}
			}
		}

		if ( ! $file_exists && ! file_exists($_atu_path))
		{
			show_error('Unable to load the requested file: '.$_atu_file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.

		$ATU =& get_instance();
		foreach (get_object_vars($ATU) as $_atu_key => $_atu_var)
		{
			if ( ! isset($this->$_atu_key))
			{
				$this->$_atu_key =& $ATU->$_atu_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_atu_vars))
		{
			$this->_atu_cached_vars = array_merge($this->_atu_cached_vars, $_atu_vars);
		}
		extract($this->_atu_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.

		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_atu_path))));
		}
		else
		{
			include($_atu_path); // include() vs include_once() allows for multiple views with the same name
		}

		log_message('debug', 'File loaded: '.$_atu_path);

		// Return the file data if requested
		if ($_atu_return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_atu_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$ATU->output->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @param	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	protected function _atu_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, $last_slash + 1);

			// Get the filename from the path
			$class = substr($class, $last_slash + 1);
		}

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$subclass = BASEPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';
			
			// Is this a class extension request?
			if (file_exists($subclass))
			{
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';
				
				if ( ! file_exists($baseclass))
				{
					log_message('error', "Unable to load the requested class: ".$class);
					show_error("Unable to load the requested class: ".$class);
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($subclass, $this->_atu_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null($object_name))
					{
						$ATU =& get_instance();
						if ( ! isset($ATU->$object_name))
						{
							return $this->_atu_init_class($class, config_item('subclass_prefix'), $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}

				include_once($baseclass);
				include_once($subclass);
				$this->_atu_loaded_files[] = $subclass;

				return $this->_atu_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			}

			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			foreach ($this->_atu_library_paths as $path)
			{
				$filepath = $path.'libraries/'.$subdir.$class.'.php';

				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_atu_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null($object_name))
					{
						$ATU =& get_instance();
						if ( ! isset($ATU->$object_name))
						{
							return $this->_atu_init_class($class, '', $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;
				}

				include_once($filepath);
				$this->_atu_loaded_files[] = $filepath;
				return $this->_atu_init_class($class, '', $params, $object_name);
			}

		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_atu_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @param	string	an optional object name
	 * @return	null
	 */
	protected function _atu_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{
		$config_component = $this->_atu_get_component('config');
		

		if ($prefix == '')
		{
			if (class_exists('ATU_'.$class))
			{
				$name = 'ATU_'.$class;
			}
			elseif (class_exists(config_item('subclass_prefix').$class))
			{
				$name = config_item('subclass_prefix').$class;
			}
			else
			{
				$name = $class;
			}
		}
		else
		{
			$name = $prefix.$class;
		}

		// Is the class name valid?
		if ( ! class_exists($name))
		{
			log_message('error', "Non-existent class: ".$name);
			show_error("Non-existent class: ".$class);
		}

		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);

		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_atu_varmap[$class])) ? $class : $this->_atu_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}

		// Save the class name and object name
		$this->_atu_classes[$class] = $classvar;

		// Instantiate the class
		$ATU =& get_instance();
		if ($config !== NULL)
		{
			$ATU->$classvar = new $name($config);
		}
		else
		{
			$ATU->$classvar = new $name;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, and helpers to be loaded automatically.
	 *
	 * @param	array
	 * @return	void
	 */
	private function _atu_autoloader()
	{
		
		include(APPPATH.'config.php');
		
		if ( ! isset($autoload))
		{
			return FALSE;
		}



		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			/*
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}
			*/

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param	object
	 * @return	array
	 */
	protected function _atu_object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}

	// --------------------------------------------------------------------

	/**
	 * Get a reference to a specific library or model
	 *
	 * @param 	string
	 * @return	bool
	 */
	protected function &_atu_get_component($component)
	{
		$ATU =& get_instance();
		return $ATU->$component;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep filename
	 *
	 * This function preps the name of various items to make loading them more reliable.
	 *
	 * @param	mixed
	 * @param 	string
	 * @return	array
	 */
	protected function _atu_prep_filename($filename, $extension)
	{
		if ( ! is_array($filename))
		{
			return array(strtolower(str_replace('.php', '', str_replace($extension, '', $filename)).$extension));
		}
		else
		{
			foreach ($filename as $key => $val)
			{
				$filename[$key] = strtolower(str_replace('.php', '', str_replace($extension, '', $val)).$extension);
			}

			return $filename;
		}
	}
}
