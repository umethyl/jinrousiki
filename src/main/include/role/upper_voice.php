<?php
/*
  ◆メガホン (upper_voice)
  ○仕様
  ・声の大きさが一段階大きく発言され、大声は音割れしてしまう
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_upper_voice extends RoleTalkFilter{
  function Role_upper_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $this->ChangeVolume('up', $volume, $sentence);
  }
}
