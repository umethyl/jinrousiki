<?php
/*
  ◆小声 (weak_voice)
  ○仕様
  ・声の大きさが常時「小声」で固定される
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_weak_voice extends RoleTalkFilter{
  function Role_weak_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $volume = 'weak';
  }
}
