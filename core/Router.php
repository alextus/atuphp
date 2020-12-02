<?php
class ATU_Router{
	var $config;
	var $directory	= '';
	var $class      ="index";       
	var $method     ="index";
	var $vars       =array();
	function __construct()
	{
		$this->config=& get_config();

		if(empty($this->config["routes"])){
			$index_page = 'index';
			$replace   = array(".html" => "", ".php" => "");
		}else{
			$index_page= $this->config["routes"]["index_page"];
			$replace   = $this->config["routes"]["replace"];
		}

		$this->default_controller= empty($index_page)?'index': $index_page;
		$this->routes_replace = empty($replace)? array(".html" => "", ".php" => ""): $replace;
		$this->isNotFound=false;
		
	}
	private function path_info(){
		//兼容phpstudy,path_info为空
		if(isset($_SERVER['PATH_INFO'])){
			$_path_info = $_SERVER['PATH_INFO'];
			
		}elseif(isset($_SERVER['REDIRECT_PATH_INFO'])){
			$_path_info = $_SERVER['REDIRECT_PATH_INFO'];
		
		}elseif(isset($_SERVER['REDIRECT_URL'])){
			$_path_info = $_SERVER['REDIRECT_URL'];
		
		}elseif(isset($_SERVER['ORIG_PATH_INFO'])){
			$_path_info = $_SERVER['ORIG_PATH_INFO'];
			
		}
		if(empty($_path_info)){
			return '/index';
		}else{
			return $_path_info;
		}

	}
	public function _set_routing(){
		//分析 directory、class、method
	
		$path=$this->path_info();
		$needExplodeEnd=0;
		foreach($this->routes_replace as $k =>$v){
			if(strpos($path,$k)){
				$needExplodeEnd=1;
			}
			$path = str_replace($k, $v, $path);
		}
		/*
		$path=str_replace(".html","",$path);
		$path = str_replace(".php", "", $path);
		*/
		//echo $path;
		
		if($path==""){return;}
		$segments = explode('/', $path);
		$temp = array('dir' => array(), 'path' => APPPATH.'controllers/');
		//print_r($segments);
		//排除目录
		foreach($segments as $k => $v)
		{
			
			$temp['path'] .= $v.'/';
		
			if(is_dir($temp['path']))
			{
				$temp['dir'][] = $v;
				unset($segments[$k]);
			}
		}
	
 		$directory=implode('/', $temp['dir']);
		
		$this->set_directory($directory);

		//最后一个元素 排除.
		if($needExplodeEnd){
			$end_segments=array_pop($segments);
			if(strpos($end_segments,".")){
				$segments=array_merge($segments,explode (".",$end_segments));
			}else{
				$segments[]=$end_segments;
			}
		}
		
		$segments = array_values($segments);
	
		//array_values  返回数组，但不包含键名
		unset($temp);

		//print_r($segments);exit;
		
		if (count($segments) > 0){
				
				/*
				echo $this->fetch_directory()."<br/>";
				print_r($segments);
				*/
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].'.php')){
					//最后一个参数文件不存在，从默认文件中找
					if (file_exists(APPPATH . 'controllers/' . $this->fetch_directory() . 'index.php')) {
						
						$this->set_class_method_var("index", $segments[0]==""?"index":$segments[0],$segments);
					} else {
						show_404($this->fetch_directory().$segments[0],"404");
					}
					
				}else{
				
					if($segments[0]=="index" && count($segments) > 1 &&  (file_exists(APPPATH . 'controllers/' . 	$this->fetch_directory() . $segments[1] . '.php')  )){
						
							//如果有同名文件，优先调用文件的，然后调用index的
							$c = count($segments) > 2 && $segments[2] != "";
							$this->set_class_method_var($segments[1], $c ? $segments[2]: "index", $segments,3);
						
					}else{
						$c= count($segments) > 1 && $segments[1] != "";
						$this->set_class_method_var($segments[0], $c ? $segments[1] : "index", $segments, 2);

					}
				}
					
		}
		else
		{
			
			if (is_dir(APPPATH.'controllers/'.$this->fetch_directory())){
				
				
				//$this->set_class("index");
			}else{
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php'))
				{
					//$this->set_directory('');
					echo "not found";exit;
					
				}else{
					$this->set_class($this->default_controller);
				}
			}
		}
		
		return true;
		
	}
	function set_class_method_var($class,$method,$var,$w=1){
		$this->set_class($class);
		$this->set_method($method);
		
		if (count($var) > $w) {
			$this->set_var($var, $w);
		}
	}
	function fetch_class(){
		return $this->class;
	}
	function fetch_method(){
		return $this->method;
	}
	function fetch_directory()
	{
		return $this->directory;
	}
	function set_class($v)
	{
		$this->class=$v;
	}
	function set_method($v)
	{
		if(in_array($v,array("list","print"))){
			$v.="s";
		}
		$this->method=$v;
	}
	function set_var($arr,$s){
		$v=array();
		for($i=$s;$i<count($arr);$i++){
			$v[]=$arr[$i];
		}
		
		$this->vars=$v;	
		
	}

	function set_directory($v)
	{
		//第一个位置不能为/，最后一个位置要为/
	
		$this->directory=$v.'/';
	}
	

}
