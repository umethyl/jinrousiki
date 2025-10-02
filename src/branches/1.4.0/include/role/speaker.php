<?php
/*
  ◆スピーカー (speaker)
  ○仕様
  ・声の大きさが一段階大きくなり、大声は音割れしてしまう
  ・共有者の囁きは変換対象外
  ・ゲームプレイ中で生存時のみ有効

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
class Role_speaker extends RoleTalkFilter{
  function Role_speaker(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function AddTalk($user, $talk, &$user_info, &$volume, &$sentence){
    $this->ChangeVolume('up', $volume, $sentence);
  }

  function AddWhisper($role, $talk, &$user_info, &$volume, &$sentence){
    if($role == 'wolf') $this->ChangeVolume('up', $volume, $sentence);
  }
}
