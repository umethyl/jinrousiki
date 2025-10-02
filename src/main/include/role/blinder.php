<?php
/*
  ◆目隠し (blinder)
  ○仕様
  ・発言フィルタ：自分以外の名前が見えなくなる (共有者の囁き・人狼の遠吠えには影響しない)
    - 問題点：観戦モードにすると普通に見えてしまう
*/
class Role_blinder extends Role {
  //スキップ判定
  function IgnoreTalk() {
    return  ! $this->GetViewer()->virtual_live &&
      ! DB::$USER->IsVirtualLive($this->GetViewer()->id);
  }

  //発言フィルタ
  function FilterTalk(User $user, &$name, &$voice, &$str) {
    if ($this->IgnoreTalk() || ! DB::$ROOM->IsDay() || $this->GetViewer()->IsSame($user)) {
      return;
    }
    $name = '';
  }

  //囁きフィルタ
  function FilterWhisper(&$voice, &$str) {}
}
