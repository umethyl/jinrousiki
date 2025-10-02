<?php
/*
  ◆大声 (strong_voice)
  ○仕様
  ・声の大きさが常時「大声」で固定される
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_strong_voice extends RoleTalkFilter{
  function Role_strong_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $volume = 'strong';
  }
}
