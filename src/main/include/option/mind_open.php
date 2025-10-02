<?php
/*
  ◆白夜村 (mind_open)
  ○仕様
*/
class Option_mind_open extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){ return $this->CastAll($list); }
}
