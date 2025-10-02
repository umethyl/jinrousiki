<?php
/*
  ◆探偵村 (detective)
  ○仕様
*/
class Option_detective extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    if($list['common'] > 0){
      $list['common']--;
      $list['detective_common']++;
    }
    elseif($list['human'] > 0){
      $list['human']--;
      $list['detective_common']++;
    }
  }
}
