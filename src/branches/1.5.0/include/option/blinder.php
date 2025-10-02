<?php
/*
  ◆宵闇村 (blinder)
  ○仕様
*/
class Option_blinder extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){ return $this->CastAll($list); }
}
