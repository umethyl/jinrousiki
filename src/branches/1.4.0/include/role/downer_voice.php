<?php
/*
  ◆マスク (downer_voice)
  ○仕様
  ・声の大きさが一段階小さく発言され、小声は共有者の囁きに変換されてしまう
  ・ゲームプレイ中で生存時のみ有効
*/
class Role_downer_voice extends RoleTalkFilter{
  function Role_downer_voice(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoice(&$volume, &$sentence){
    $this->ChangeVolume('down', $volume, $sentence);
  }
}
