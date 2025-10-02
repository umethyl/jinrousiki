<?php
/*
  ◆不器用 (normal_voice)
  ○仕様
  ・声の大きさが常時「普通声」で固定される
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_normal_voice extends RoleTalkFilter{
  function Role_normal_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $volume = 'normal';
  }
}
