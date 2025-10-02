<?php
class Table{
  var $name;
  var $default_charset;
  var $engine;
  var $fields = array();
  var $indices = array();

  function Table(){ $this->__construct(); }
  function __construct(){}

  function Exists($use_cache = true){
    if($use_cache){
      return isset($this->exists) ? $this->exists : ($this->exists = $this->Exists(false));
    }

    $list = FetchArray("SHOW TABLES LIKE {$this->name}");
    foreach($list as $field){
      if($field == $this->name) return true;
    }
    return false;
  }

  function Update(){}
}
