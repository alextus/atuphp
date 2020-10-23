<?php
class Alex{
    private $alphabet = 'ALEXTUaextu0123456789-';
    private $base = 0;
    public function __construct(){
        $this -> base = strlen($this -> alphabet);//计算进制数
		$this ->alexStartTime=strtotime('2016-12-19 00:00:00');
    }
	public function time(){
		return time()-$this ->alexStartTime;	
	}
	public function microtime(){
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2)-$this ->alexStartTime)*100);
		
	}
	public function codetime(){
		$nmicrotime=$this->microtime();
		//$nmicrotime=intval($this->microtime()/10);
		return $nmicrotime.rand(10,99);
	}
	public function getRealTime($time){
		$t=$time+$this ->alexStartTime;
		return date("Y-m-d H:i:s",$t);
	}
	public function getMicroTime($microtime){
		$t=intval($microtime/100)+$this ->alexStartTime;
		return date("Y-m-d H:i:s",$t);
	}
	public function getCodeTime($code){
		$t=$this->decodetime($code);
		return date("Y-m-d H:i:s",$this->getMicroTime($t));
	}
	
	public function code(){
		//前3验证码，1，为alextu中随机1个字符，2，为随后的34取模
		$t=floatval($this->codetime());
		$y=10+fmod($t,34);
		$r=array("a","l","e","x","t","u");
		$s=$r[rand(0,sizeof($r)-1)];
		return $s.$y.base_convert($t,10,36);
	}
	public function isCode($code){
		$r=array("a","l","e","x","t","u");
		$s=substr($code,0,1);
		$y=substr($code,1,2);
		$t1=substr($code,3,strlen($code));
		$t2=$this->decode($code);
		
		if( in_array($s,$r) && $y==10+fmod(floatval($t2),34)){
			return true;	
		}else{
			return false;	
		}
		
	}
	public function decode($code){
		return base_convert(substr($code,3,strlen($code)),36,10);
	}
	public function decodetime($code){
		//返回code对应的codetime ,去前三后二
		$t=substr($code,3,strlen($code));
		
		$rt=base_convert($t,36,10);
		
		return substr($rt,0,strlen($rt)-2);
	}
	public function isExprie($code,$dt=60){
		//判断是否过期，默认60秒
		$t=$this->getExprieTime($code);
	
		if($t>$dt*100){
			return true;	
		}else{
			return false;
		}
		
	}
	public function getExprieTime($code){
		//判断是否过期，默认60秒
		$t=bcsub($this->microtime(),$this->decodetime($code));
		
		return $t;
		
	}
	
	
	//文件操作相关
	//随机读取文件夹中某个文件
	public function getRandomFile($folder='', $extensions='.*'){
	
		  $folder = trim($folder);
		  $folder = ($folder == '') ? './' : $folder;
		
		  if (!is_dir($folder)){ return ""; }
		
		  $files = $this->getFolderFiles($folder,$extensions);
		
		  if(count($files) == 0){
			return "";
		  }
		 
		  mt_srand((double)microtime()*1000000);
		 
		  $rand = mt_rand(0, count($files)-1);
		  
		  if (!isset($files[$rand])){
			return "";
		  }
		  return $folder.$files[$rand] ;
	}
	public function getFolderFiles($folder='',$extensions='.*'){
		$folder = trim($folder);
		$folder = ($folder == '') ? './' : $folder;
		
		$files = array();
		if ($dir = @opendir($folder)){
			while($file = readdir($dir)){
			  if (!preg_match('/^\.+$/', $file) and
				  preg_match('/\.('.$extensions.')$/', $file)){
				  $files[] = $file;        
			  }      
			}    
			closedir($dir); 
		}
		return $files;
		
	}
	public function getFolderFilesNum($folder='',$extensions='.*'){
		$folderFile=$this->getFolderFiles($folder,$extensions);
		return count($folderFile);
	}
	
	public function addFile($folderFile,$content){
		
	}
  
}
		
