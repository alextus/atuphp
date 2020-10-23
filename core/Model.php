<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATU_Model {

	var $ATU;
	
	function __construct()
	{
		//log_message('debug', "Model Class Initialized");
	
	}


	function __get($key)
	{
		$ATU =& get_instance();
		return $ATU->$key;
	}
	
	
	public function cookie($name,$value=null,$exitTime=0){
		$ATU =& get_instance();
		return $ATU->_cookie($name,$value,$exitTime);	
	}
	
}
