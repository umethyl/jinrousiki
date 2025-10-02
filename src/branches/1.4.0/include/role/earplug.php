<?php
/*
  ◆耳栓 (earplug)
  ○仕様
  ・声の大きさが一段階小さくなり、小声は共有者の囁きに見える
  ・共有者の囁きは変換対象外
  ・ゲームプレイ中で生存時のみ有効

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
class Role_earplug extends RoleTalkFilter{
  function Role_earplug(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function Ignored(){
    global $ROOM;
    return parent::Ignored() ||
      ($ROOM->log_mode && $ROOM->IsEvent('earplug') && ! $ROOM->IsDay());
  }

  function AddTalk($user, $talk, &$user_info, &$volume, &$sentence){
    $this->ChangeVolume('down', $volume, $sentence);
  }

  function AddWhisper($role, $talk, &$user_info, &$volume, &$sentence){
    if($role == 'wolf') $this->ChangeVolume('down', $volume, $sentence);
  }
}
