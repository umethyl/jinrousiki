<?php
/*
  ◆急所村 (critical)
  ○仕様
*/
class Option_critical extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){
    foreach(array_keys($list) as $id) $list[$id] .= ' critical_voter critical_luck';
    return array('critical_voter', 'critical_luck');
  }
}
