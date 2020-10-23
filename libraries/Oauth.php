<?php 
/*
*  JOSN通讯接口
*/
class ATU_Oauth { 
	
 
    function __construct()
    {
       
    }
	public function combineURL($baseURL,$keysArr){
	   return $baseURL."?".http_build_query($keysArr);
	}
	public function http($url, $postfields='', $method='GET', $headers=array()){ 

         $curl=curl_init(); 
		 
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
         curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); 
         curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
         if($method=='POST'){ 
             curl_setopt($curl, CURLOPT_POST, TRUE); 
             if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields); 
         } 
         $headers[]="User-Agent: sinaPHP(piscdong.com)"; 
         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
         curl_setopt($curl, CURLOPT_URL, $url); 
		 /*
		 简略版
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		 */ 

         $response=curl_exec($curl); 
         curl_close($curl); 
         return $response; 

     } 
	 public function get($url){
		 $d=$this->http($url);
		 if(substr( $d, 0, 1 )=="("){
			 $d="[$d]";
			 $d=str_replace("[(","",$d);
			 $d=str_replace(")]","",$d);
		}
		//echo $url;
		return $d;
	 }
 
}

?>