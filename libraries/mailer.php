<?php  
  
class mailer {
  
     var $mail;
	 protected $_date_fmt	= 'Y-m-d H:i:s';
  
     public function __construct()
     {
		 $config =& get_config();
		 
		 $this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';
         require_once('phpmailer/class.phpmailer.php');
  		 date_default_timezone_set('Asia/Shanghai');//设定时区东八区

         // the true param means it will throw exceptions on errors, which we need to catch
         $this->mail = new PHPMailer(true);
  
         $this->mail->IsSMTP(); // telling the class to use SMTP
  		 $this->mail->IsHTML(true); 
         $this->mail->CharSet    = "utf-8";                  // 一定要設定 CharSet 才能正確處理中文
         $this->mail->SMTPDebug  = 0;                     // enables SMTP debug information
         $this->mail->SMTPAuth   = true;                  // enable SMTP authentication
         $this->mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
         $this->mail->Host       = "smtp.exmail.qq.com";      // sets GMAIL as the SMTP server
         $this->mail->Port       = 465;                   // set the SMTP port for the GMAIL server
         $this->mail->Username   = "jingyi@leanone.cn";// GMAIL username
         $this->mail->Password   = "leanone123456";       // GMAIL password
         $this->mail->AddReplyTo('jingyi@leanone.cn', '精一学社');
         $this->mail->SetFrom('jingyi@leanone.cn', '精一学社');
		
     }
  
     public function sendmail($to, $to_name, $subject, $body){
         try{
             $this->mail->AddAddress($to, $to_name);
  
             $this->mail->Subject = $subject;
             $this->mail->Body    = $body;
  
             $this->mail->Send();
             $this->mail_log("suc","邮件发送成功,to:$to,toName:$to_name,subject:$subject,body:$body");
  
         } catch (phpmailerException $e) {
             $this-> mail_log("err1", $e->errorMessage().",to:$to"); //Pretty error messages from PHPMailer
         } catch (Exception $e) {
             $this->mail_log("err2", $e->getMessage().",to:$to"); //Boring error messages from anything else!
         }
		 
     }
	 
	 public function mail_log($level,$msg)
	{
		

		
	
		$filepath = $this->_log_path.'mail-'.date('Y-m-d').'.php';
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php   ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		$message .= $level.' '.date($this->_date_fmt). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}
}




