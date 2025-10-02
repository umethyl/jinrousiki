<?php
/*
  ◆目隠し (blinder)
  ○仕様
  ・自分以外のハンドルネームが見えなくなる
  ・人狼の遠吠え、共有者のひそひそ声には影響しない
  ・ゲームプレイ中で生存時のみ有効

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
class Role_blinder extends RoleTalkFilter{
  function Role_blinder(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function AddTalk($user, $talk, &$user_info, &$volume, &$sentence){
    global $ROOM;

    if($this->Ignored() || ! $ROOM->IsDay() || $this->IsSameUser($user->uname)) return;
    $user_info = '<font style="color:' . $user->color . '">◆</font>';
  }
}
