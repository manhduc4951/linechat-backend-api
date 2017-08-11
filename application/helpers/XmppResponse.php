<?php

class XmppResponse
{
    
    const SUCCESS = '0';
    
    protected $code;
    
    protected $data = array();
    
    public function __get($name) {
        if ($name == 'code') {
            return $this->code;
        }
        
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        return null;
    }
    
    public function __set($name, $value) {
        if ($name == 'code') {
            $this->code = $value;
        }
        
        $this->data[$name] = $value;
    }
    
    public function __construct($code = true, $data = array())
    {
        if ($code === true) {
            $code = self::SUCCESS;
        }
        
        $this->code = (string) $code;
        $this->data = $data;
    }
    
    public function isSuccess()
    {
        return $this->code == self::SUCCESS;
    }
    
    public function isFailure()
    {
        return $this->code != self::SUCCESS;
    }
    
    public function getCode()
    {
        return $this->code;
    }
}