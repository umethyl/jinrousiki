<?php
/*
  ◆静寂村 (deep_sleep)
  ○仕様
*/
class Option_deep_sleep extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){ return $this->CastAll($list); }
}
