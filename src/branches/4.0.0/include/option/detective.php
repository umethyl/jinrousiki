<?php
/*
  ◆探偵村 (detective)
  ○仕様
  ・配役：探偵 (神話マニア ＞ 共有者 ＞ 村人)
*/
class Option_detective extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '探偵村';
  }

  public function GetExplain() {
    return '「探偵」が登場し、初日の夜に全員に公表されます';
  }

  public function SetRole(array &$list, $count) {
    $target_role = $this->GetTargetRole();
    foreach (['mania', 'common', 'human'] as $role) {
      if (OptionManager::Replace($list, $role, $target_role)) break;
    }
  }

  //闇鍋固定枠追加
  public function FilterChaosFixRole(array &$list) {
    ArrayFilter::Initialize($list, $this->GetTargetRole(), 1);
  }

  //指名処理
  public function Designate() {
    //Cast::Stack()->p(Cast::DETECTIVE, '◆detective');
    $stack = Cast::Stack()->Get(Cast::DETECTIVE);
    Cast::Stack()->Clear(Cast::DETECTIVE);
    if (1 > count($stack)) return;

    $user = Lottery::Get($stack);
    RoomTalk::StoreSystem(sprintf(TalkMessage::DETECTIVE, $user->handle_name));

    //霊界探偵モードなら探偵を霊界に送る
    if (DB::$ROOM->IsOption('gm_login') && DB::$ROOM->IsOption('not_open_cast') &&
	Cast::Stack()->Get(Cast::COUNT) > 7) {
      $user->ToDead();
    }
  }

  //対象役職取得
  private function GetTargetRole() {
    return 'detective_common';
  }
}
