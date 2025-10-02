<?php
/*
  ◆外弁慶 (outside_voice)
  ○仕様
  ・声の大きさが昼は「大声」、夜は「小声」で固定される
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_outside_voice extends RoleTalkFilter{
  function Role_outside_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    global $ROOM;
    $volume = $ROOM->IsDay() ? 'strong' : 'weak';
  }
}
