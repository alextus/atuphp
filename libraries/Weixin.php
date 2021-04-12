<?php 

class ATU_Weixin extends ATU_Oauth{ 
	
	var $AppID=""; 
	var $AppSecret = ""; 
    function __construct($appID, $appSecret)
    {
        $this->weixin($appID, $appSecret);
    }
	function weixin($appID, $appSecret){
		 $this->AppID=$appID;
		 $this->AppSecret=$appSecret;
		
         $this->access_token=""; 
	}
	
	/* step1 
	   页面跳转，获取code
	*/
	//pc 网页获得授权登录
	public function getLoginUrl($callBack,$type="snsapi_login",$state){ 
		
         $params=array( 	
             'appid'=> $this->AppID, 
             'redirect_uri'=>$callBack, 
             'response_type'=>"code" , 
             'scope'=>$type ,
             'state'=>$state 
         ); 
		 return $this->combineURL("https://open.weixin.qq.com/connect/qrconnect",$params); 

	}
	//微信端获取用户OpenID
	function getConnectUrl($callBack,$type=0,$state=""){
		
		$params=array( 	
             'appid'=> $this->AppID, 
             'redirect_uri'=>$callBack, 
             'response_type'=>"code" , 
             'scope'=>$type?"snsapi_userinfo":"snsapi_base" ,
             'state'=>$state==""?time():$state
         ); 
		$url =$this->combineURL("https://open.weixin.qq.com/connect/oauth2/authorize",$params); 
		return $url."#wechat_redirect";
	}
	
	
	
	
	/* step2 
	   根据微信返回code 获取用户access_token,refresh_token,openid和 union通用
	   access_token 时效2小时,refresh_token时效7天，本请求不限制
	  + 如果不记录用户数据的情况下，可使用refresh_token 代替  getToken +
	*/
	public function _getToken($code){
		
		$params=array( 	
             'appid'=> $this->AppID, 
             'secret'=>$this->AppSecret, 
             'code'=>$code, 
             'grant_type'=>"authorization_code" ,
             't'=>time()
         ); 
		$url =$this->combineURL("https://api.weixin.qq.com/sns/oauth2/access_token",$params); 
		
		return $this->http($url);
		
	}
	public  function _getRefreshToken($refresh_token){ 

         $params=array( 	
             'appid'=> $this->AppID, 
             'grant_type'=> "refresh_token",
             'refresh_token'=>$refresh_token 
         ); 
		 return $this->http($this->combineURL("https://api.weixin.qq.com/sns/oauth2/refresh_token",$params)); 

     } 
	 
	
	
	
	public function _getLogin($data){
		
		
		if(isset($data["access_token"])){ 
		
			$expire=time()+120*24*60*60;
			setcookie("access_token", $data["access_token"], $data["expires_in"]);
			setcookie("refresh_token", $data["refresh_token"], $expire);
			setcookie("openid", $data["openid"], $expire);
			setcookie("union", $data["union"], $expire);
	
			return $this->_getUserInfo($data["access_token"], $data["openid"]);
		}else{
		
			return $false;
		}
	}
	/* step3
	    根据微信返回access_token和openid/ union 获取用户数据
	    subscribe	用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。
		openid	用户的标识，对当前公众号唯一
		nickname	用户的昵称
		sex	用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
		city	用户所在城市
		country	用户所在国家
		province	用户所在省份
		language	用户的语言，简体中文为zh_CN
		headimgurl	用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
		subscribe_time	用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
		unionid	只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。详见：获取用户个人信息（UnionID机制）
		remark	公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注
		groupid 用户所在的分组ID
	*/
	//获取用户信息
	public function _getUserInfo($access_token,$openid){
		
		$params=array( 	
             'access_token'=> $access_token, 
             'openid'=>$openid, 
             'lang'=>"zh_CN"
        ); 
		$url =$this->combineURL("https://api.weixin.qq.com/sns/userinfo",$params); 
		$d=json_decode($this->http($url),TRUE);
		
		$fp = fopen("data/log/$openid.json", "w");
		fwrite($fp, json_encode($d));
		fclose($fp);
				 	
		return $d;
		
	}
	
	//后台获取用户信息
	public function getUserBaseInfo($accessToken,$openid){
		$params=array( 	
             'access_token'=> $accessToken, 
             'openid'=>$openid, 
             'lang'=>"zh_CN"
         ); 
		$url =$this->combineURL("https://api.weixin.qq.com/cgi-bin/user/info",$params); 
		$d=json_decode($this->http($url),TRUE);
		return $d;
		
	}
	/*
		后台获取OpenList,最多 10000
		返回 total
		    count
			data.openid["",""]
			next_openid
	*/
	public function getOpenIdList($accessToken,$openid=""){
		$params=array( 	
             'access_token'=> $accessToken, 
             'next_openid'=>$openid
         ); 
		$url =$this->combineURL("https://api.weixin.qq.com/cgi-bin/user/get",$params); 
		$d=json_decode($this->http($url),TRUE);
		return $d;
		
	}
	
	/*
		获取原生access_token,JS自定义分享、菜单用，每日限制2000次
		正常返回：{"access_token":"ACCESS_TOKEN","expires_in":7200}
		错误返回：{"errcode":40013,"errmsg":"invalid appid"}
	*/
	public function _getAccessToken($path="wx") {
		$dataUrl="$path/access_token.json";
		if(is_file($dataUrl)){
			$data = json_decode(file_get_contents($dataUrl));
			if ($data->expire_time < time()) {
				 $access_token =$this->createAccessToken($dataUrl);
			}else{
				 $access_token = $data->access_token;
			}
		}else{
			$access_token =$this->createAccessToken($dataUrl);
		}
		return $access_token;
	}
	private function createAccessToken($dataUrl){
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->AppID.'&secret='.$this->AppSecret;
			
			//获得 access_token,expires_in
			$res=json_decode($this->http($url),TRUE);
		
			$access_token = $res["access_token"];
		
			if ($access_token) {
				 $data=array(
				 	"expire_time" => time() + 7200,
					"access_token"=>$access_token,
					"access_token"=>$access_token
				 );
				 $fp = fopen($dataUrl, "w");
				 fwrite($fp, json_encode($data));
				 fclose($fp);	
			}
			return $access_token;
	}
	//开票功能，有待研究
	public function getSpappid($access_token){
		$url = 'https://api.weixin.qq.com/card/invoice/seturl?access_token='. $access_token;

		//获得 access_token,expires_in
		$res = json_decode($this->postJosn($url, array()), TRUE);

	
		
		return $res;
	}	
	
	/*JSSDK*/
	
	public function getSignPackage($path="wx",$url) {
		$jsapiTicket = $this->getJsApiTicket($path);
	  // 	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();
	
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
	
		$signature = sha1($string);
	
		$signPackage = array(
		  "appId"     => $this->AppID,
		  "nonceStr"  => $nonceStr,
		  "timestamp" => $timestamp,
		  "url"       => $url,
		  "signature" => $signature,
		  "rawString" => $string
		);
		
		$fp = fopen("$path/signature.json", "w");
		fwrite($fp, json_encode($signPackage));
		fclose($fp);
		
		return $signPackage; 
   }
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
		  $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	 }
	private function getJsApiTicket($path="wx") {
		// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
		$dataUrl="$path/jsapi_ticket.json";
		
		if(is_file($dataUrl)){
			$data = json_decode(file_get_contents($dataUrl));
			if ($data->expire_time < time()) {
			  $ticket=$this->createJsApiTicket($path);
			} else {
			   $ticket = $data->jsapi_ticket;
			}
		}else{
			$ticket=$this->createJsApiTicket($path);
		}
		return $ticket;
	}
	private function createJsApiTicket($path="wx"){
		  $dataUrl="$path/jsapi_ticket.json";
		  $accessToken = $this->_getAccessToken($path);
		  $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		  $res = json_decode($this->http($url));
		  $ticket = $res->ticket;
		 
		  if ($ticket) {
			$data=array(
				"expire_time"=>time() + 7000,
				"jsapi_ticket"=>$ticket
			);
			$fp = fopen($dataUrl, "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		  }
		  return $ticket;
	}

	//清空变量
	public function clear(){
		setcookie("access_token", '', 0);
		setcookie("refresh_token",'', 0);
		setcookie("openid", '', 0);
		setcookie("union", '', 0);
		
	}
	
	
 
}


/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

class ErrorCode
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
}

class WXBizDataCrypt
{
	private $appid;
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $appid string 小程序的appid
	 */
	public function __construct($appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData($encryptedData, $iv, &$data)
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey = base64_decode($this->sessionKey);


		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV = base64_decode($iv);

		$aesCipher = base64_decode($encryptedData);

		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj = json_decode($result);
		if ($dataObj  == NULL) {
			return ErrorCode::$IllegalBuffer;
		}
		if ($dataObj->watermark->appid != $this->appid) {
			return ErrorCode::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode::$OK;
	}
}


?>