<?php

class ATU_Model
{
    public $ATU;
    
    public function __construct()
    {
        //log_message('debug', "Model Class Initialized");
        $this->https=new ATU_Http();
    }

    public function __get($key)
    {
        $ATU =& get_instance();
        return $ATU->$key;
    }
    
    
    public function cookie($name, $value=null, $exitTime=0)
    {
        $ATU =& get_instance();
        return $ATU->_cookie($name, $value, $exitTime);
    }
}
