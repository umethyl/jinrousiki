<?php
/*
  ◆ジョーカー村 (joker)
  ○仕様
*/
class Option_joker extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){ $this->CastOnce($list, $rand, '[2]'); }
}
