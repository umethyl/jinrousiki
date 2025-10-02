<?php
/*
  ◆天邪鬼村 (perverseness)
  ○仕様
*/
class Option_perverseness extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){ return $this->CastAll($list); }
}
