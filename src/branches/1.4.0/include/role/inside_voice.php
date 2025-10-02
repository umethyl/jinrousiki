<?php
/*
  ◆内弁慶 (inside_voice)
  ○仕様
  ・声の大きさが昼は「小声」、夜は「大声」で固定される
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_inside_voice extends RoleTalkFilter{
  function Role_inside_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    global $ROOM;
    $volume = $ROOM->IsDay() ? 'weak' : 'strong';
  }
}
