<?php
//オプションパーサ
class OptionParser{
  public $row;
  public $options = array();

  function __construct($value){
    $this->row = $value;
    foreach(explode(' ', $this->row) as $option){
      if(empty($option)) continue;
      $items = explode(':', $option);
      $this->options[$items[0]] = count($items) > 1 ? array_slice($items, 1) : true;
    }
  }

  function __get($name){
    $this->$name = array_key_exists($name, $this->options) ? $this->options[$name] : false;
    return $this->$name;
  }

  function __set($name, $value){
    if($value === false)
      unset($this->options[$name]);
    else
      $this->options[$name] = $value;
  }

  function __toString(){
    return '';
    $result = '';
    foreach($this->options as $name => $value){
      $result = ' ' . is_array($value) ? "{$name}:" . implode(':', $value) : $name;
    }
    return $result;
  }

  function Option($value){
    $this->__construct($value);
    foreach($this->options as $name => $value) $this->__get($name);
  }

  function Exists($name){ return array_key_exists($name, $this->options); }
}

//オプションマネージャ
class OptionManager{
  public $path;
  public $stack;
  public $loaded;

  //特殊普通村編成リスト
  public $role_list = array(
    'detective', 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'possessed_wolf',
    'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania');

  //特殊サブ配役リスト
  public $cast_list = array(
    'decide', 'authority', 'joker', 'deep_sleep', 'blinder', 'mind_open',
    'perverseness', 'liar', 'gentleman', 'critical', 'sudden_death', 'quiz');

  function __construct(){
    $this->path = JINRO_INC . '/option';
    $this->stack  = new StdClass();
    $this->loaded = array();
  }

  protected function Load($name){
    if(is_null($name) || ! file_exists($file = $this->path . '/' . $name . '.php')) return false;
    if(in_array($name, $this->loaded)) return true;
    require_once($file);
    $this->loaded[] = $name;
    return true;
  }

  function SetRole(&$list, $count){
    global $ROOM;

    foreach($this->role_list as $option){
      if(! $ROOM->IsOption($option) || ! $this->Load($option)) continue;
      $class  = 'Option_' . $option;
      $filter = new $class();
      $filter->SetRole($list, $count);
    }
  }

  function Cast(&$list, &$rand){
    global $ROOM;

    $delete = $this->stack->delete;
    foreach($this->cast_list as $option){
      if(! $ROOM->IsOption($option) || ! $this->Load($option)) continue;
      $class  = 'Option_' . $option;
      $filter = new $class();
      $stack  = $filter->Cast($list, $rand);
      if(is_array($stack)) $delete = array_merge($delete, $stack);
    }
    $this->stack->delete = $delete;
  }
}

//オプション基底クラス
class Option{
  public $name;

  function __construct(){ $this->name = array_pop(explode('Option_', get_class($this))); }

  function CastOnce(&$list, &$rand, $str = ''){
    $list[array_pop($rand)] .= ' ' . $this->name . $str;
    return array($this->name);
  }

  function CastAll(&$list){
    foreach(array_keys($list) as $id) $list[$id] .= ' ' . $this->name;
    return array($this->name);
  }
}