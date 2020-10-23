<?php

class AlexData {


	private $path="../data/"; 
	function __construct()	
	{
		$this->Batch = date("YmdHis");
		$this->key="alex.tu@2017";
		$this->t=time();
		$this->c=md5($this->t.$this->key);
		$this->tokenUrl="http://123.56.231.136/weichuan/gateway0902.php?t=".$this->t."&c=".$this->c;

					
	}
	function set($f,$d){
		$file=$this->path."$f.json";
		
		if(file_exists($file)){
			$f=file_put_contents($file,json_encode($d));
		}else{
			return array();	
		}
	}
	function get($f){
		$file=$this->path."$f.json";
		if(file_exists($file)){
			$f=file_get_contents($files);
			return json_decode($f,true);
		}else{
			return array();	
		}
	}
	function query($keysArr){
		$context = stream_context_create(array('http'=>array('ignore_errors'=>true)));

		$url= $this->tokenUrl."&".http_build_query($keysArr);
	    $d=file_get_contents($url, FALSE, $context);
	    return $d;
	
		
	}
	
	
}