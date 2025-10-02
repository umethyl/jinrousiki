<?php
/*
  ◆臆病者 (random_voice)
  ○仕様
  ・声の大きさがランダムで変化する
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_random_voice extends RoleTalkFilter{
  function Role_random_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $volume = GetRandom($this->volume_list);
  }
}
