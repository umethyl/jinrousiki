<?php
/*
  ◆目隠し (blinder)
  ○仕様
  ・発言フィルタ：自分以外の名前が見えなくなる (共有者の囁き・人狼の遠吠えには影響しない)
    - 問題点：観戦モードにすると普通に見えてしまう
*/
class Role_blinder extends Role {
  //発言フィルタ
  public function FilterTalk(User $user, &$name, &$voice, &$str) {
    if ($this->IgnoreTalk() || ! DB::$ROOM->IsDay() || $this->GetViewer()->IsSame($user)) {
      return;
    }
    $name = '';
  }

  //スキップ判定
  public function IgnoreTalk() {
    $user = $this->GetViewer();
    $live = ! $user->virtual_live && ! DB::$USER->IsVirtualLive($user->id);
    return $live || $this->CallParent('AddIgnoreTalk');
  }

  //追加スキップ判定
  public function AddIgnoreTalk() {
    false;
  }

  //囁きフィルタ
  public function FilterWhisper(&$voice, &$str) {}
}
