<?php
/*
对于低版本PHP的函数修正
*/


if (!function_exists('file_get_contents'))
{
    /**
     * Require >= 4.3.0
     */
    function file_get_contents($file)
    {
        if (($fp = @fopen($file, 'rb')) === false)
        {
            return false;
        }else{
            $fsize = @filesize($file);
			$contents =$fsize?fread($fp, $fsize):'';
            fclose($fp);
            return $contents;
        }
    }
}

if (!function_exists('file_put_contents'))
{
    define('FILE_APPEND', 'FILE_APPEND');
    /**
     * Require >=5.0
     */
    function file_put_contents($file, $data, $flags = '')
    {
        $contents = (is_array($data)) ? implode('', $data) : $data;

        $mode=($flags == 'FILE_APPEND')?'ab+':'wb';
        if (($fp = @fopen($file, $mode)) === false)
        {
            return false;
        }else{
            $bytes = fwrite($fp, $contents);
            fclose($fp);
            return $bytes;
        }
    }
}
if (!function_exists('http_build_query'))
{
     /**
     * Require >=5.1.0
     */
    function http_build_query($data)
    {
         $valueArr = array();

		 foreach($data as $key => $val){
			 $valueArr[] = "$key=$val";
		 }

		 $keyStr = implode("&",$valueArr);
		 $combined .= ($keyStr);

		 return $combined;
    }
}


if (!function_exists('move_upload_file'))
{
	/**
     * Require >= 4.0.3
     */
	function move_upload_file($file_name, $target_name = '')
	{
		if (copy($file_name, $target_name))
		{
			@chmod($target_name,0755);
			return true;
		}
		return false;
	}
}

